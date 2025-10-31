<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyChatflowBundle\Repository\ConversationMessageRepository;
use Tourze\DifyChatflowBundle\Repository\ConversationRepository;
use Tourze\DifyChatflowBundle\Request\GetConversationsRequest;
use Tourze\DifyChatflowBundle\Request\GetMessagesRequest;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Service\DifyApiClient;
use Tourze\DifyCoreBundle\Service\DifyAppService;

#[AsCommand(
    name: 'dify:sync-conversations',
    description: '同步Dify应用的会话记录和消息数据'
)]
class SyncConversationsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DifyAppService $difyAppService,
        private readonly DifyApiClient $difyApiClient,
        private readonly ConversationRepository $conversationRepository,
        private readonly ConversationMessageRepository $conversationMessageRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('app-id', InputArgument::OPTIONAL, 'DifyApp ID（可选，默认同步所有有效应用）')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, '每次请求限制数量', '100')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '仅显示将要同步的数据，不实际写入数据库')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $appIdValue = $input->getArgument('app-id');
        $appId = is_string($appIdValue) ? $appIdValue : null;
        $limitValue = $input->getOption('limit');
        $limit = is_numeric($limitValue) ? (int) $limitValue : 100;
        $isDryRunValue = $input->getOption('dry-run');
        $isDryRun = is_bool($isDryRunValue) ? $isDryRunValue : false;

        $io->title('Dify会话数据同步');

        // 获取要同步的应用
        $apps = $this->getAppsToSync($appId);
        if ([] === $apps) {
            $io->error('未找到有效的Dify应用配置');

            return Command::FAILURE;
        }

        $io->info(sprintf('找到 %d 个应用需要同步', count($apps)));

        $totalConversations = 0;
        $totalMessages = 0;

        foreach ($apps as $app) {
            $io->section(sprintf('同步应用: %s (%s)', $app->getName(), $app->getId()));

            try {
                $result = $this->syncAppConversations($app, $limit, $isDryRun, $io);
                $totalConversations += $result['conversations'];
                $totalMessages += $result['messages'];
            } catch (\Throwable $e) {
                $io->error(sprintf('应用 %s 同步失败: %s', $app->getName(), $e->getMessage()));
                continue;
            }
        }

        $io->success(sprintf(
            '同步完成！共处理 %d 个会话，%d 条消息',
            $totalConversations,
            $totalMessages
        ));

        return Command::SUCCESS;
    }

    /**
     * @return DifyApp[]
     */
    private function getAppsToSync(?string $appId): array
    {
        return $this->difyAppService->getAppsToSync($appId);
    }

    /**
     * @return array{conversations: int, messages: int}
     */
    private function syncAppConversations(
        DifyApp $app,
        int $limit,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $this->validateApp($app);
        $this->difyApiClient->setApp($app);

        $syncResult = $this->processPaginatedConversations($app, $limit, $isDryRun, $io);

        if (!$isDryRun) {
            $this->entityManager->flush();
        }

        return $syncResult;
    }

    private function validateApp(DifyApp $app): void
    {
        if (null === $app->getId()) {
            throw new \RuntimeException('App ID cannot be null');
        }
    }

    /**
     * @return array{conversations: int, messages: int}
     */
    private function processPaginatedConversations(
        DifyApp $app,
        int $limit,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $conversationCount = 0;
        $messageCount = 0;
        $lastId = null;

        do {
            $userId = $app->getId();
            assert(null !== $userId); // Already validated in validateApp

            $conversationsData = $this->fetchConversations($userId, $lastId, $limit);

            if (!$this->hasValidConversationsData($conversationsData)) {
                break;
            }

            $conversationDataArray = $conversationsData['data'];
            assert(is_array($conversationDataArray));
            /** @var array<array<string, mixed>> $conversationDataArray */
            $batchResult = $this->processConversationBatch(
                $app,
                $conversationDataArray,
                $isDryRun,
                $io
            );

            $conversationCount += $batchResult['conversations'];
            $messageCount += $batchResult['messages'];

            $lastId = $this->getNextPageId($conversationsData);
        } while ($lastId);

        return ['conversations' => $conversationCount, 'messages' => $messageCount];
    }

    /**
     * @param array<string, mixed> $conversationsData
     */
    private function hasValidConversationsData(array $conversationsData): bool
    {
        return is_array($conversationsData['data'] ?? null) && [] !== $conversationsData['data'];
    }

    /**
     * @param array<string, mixed> $conversationsData
     */
    private function getNextPageId(array $conversationsData): ?string
    {
        $hasMore = $conversationsData['has_more'] ?? false;

        if (!is_bool($hasMore) || !$hasMore) {
            return null;
        }

        if (!is_array($conversationsData['data']) || [] === $conversationsData['data']) {
            return null;
        }

        $lastItem = end($conversationsData['data']);

        return is_array($lastItem) && isset($lastItem['id']) && is_string($lastItem['id'])
            ? $lastItem['id']
            : null;
    }

    /**
     * @param array<array<string, mixed>> $conversationsData
     * @return array{conversations: int, messages: int}
     */
    private function processConversationBatch(
        DifyApp $app,
        array $conversationsData,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $conversationCount = 0;
        $messageCount = 0;

        foreach ($conversationsData as $conversationData) {
            if (!is_array($conversationData)) {
                continue;
            }

            $this->processConversation($app, $conversationData, $isDryRun, $io);
            ++$conversationCount;

            $messageCount += $this->processConversationMessages(
                $app,
                $conversationData,
                $isDryRun,
                $io
            );
        }

        return ['conversations' => $conversationCount, 'messages' => $messageCount];
    }

    /**
     * @param array<string, mixed> $conversationData
     */
    private function processConversation(
        DifyApp $app,
        array $conversationData,
        bool $isDryRun,
        SymfonyStyle $io,
    ): void {
        if ($isDryRun) {
            $this->displayConversationInfo($conversationData, $io);
        } else {
            $this->saveConversation($app, $conversationData);
        }
    }

    /**
     * @param array<string, mixed> $conversationData
     */
    private function displayConversationInfo(array $conversationData, SymfonyStyle $io): void
    {
        $conversationId = $this->extractStringValue($conversationData, 'id', 'unknown');
        $conversationName = $this->extractStringValue($conversationData, 'name', 'unknown');

        $io->text(sprintf('会话: %s - %s', $conversationId, $conversationName));
    }

    /**
     * @param array<string, mixed> $conversationData
     */
    private function processConversationMessages(
        DifyApp $app,
        array $conversationData,
        bool $isDryRun,
        SymfonyStyle $io,
    ): int {
        $conversationId = $this->extractStringValue($conversationData, 'id');
        if (null === $conversationId) {
            return 0;
        }

        $appUserId = $app->getId();
        if (null === $appUserId) {
            return 0;
        }

        $messagesData = $this->fetchMessages($conversationId, $appUserId);

        if (!is_array($messagesData['data'] ?? null)) {
            return 0;
        }

        $messageDataArray = $messagesData['data'];

        /** @var array<array<string, mixed>> $messageDataArray */
        return $this->processMessageBatch($app, $messageDataArray, $isDryRun, $io);
    }

    /**
     * @param array<array<string, mixed>> $messagesData
     */
    private function processMessageBatch(
        DifyApp $app,
        array $messagesData,
        bool $isDryRun,
        SymfonyStyle $io,
    ): int {
        $messageCount = 0;

        foreach ($messagesData as $messageData) {
            if (!is_array($messageData)) {
                continue;
            }

            $this->processMessage($app, $messageData, $isDryRun, $io);
            ++$messageCount;
        }

        return $messageCount;
    }

    /**
     * @param array<string, mixed> $messageData
     */
    private function processMessage(
        DifyApp $app,
        array $messageData,
        bool $isDryRun,
        SymfonyStyle $io,
    ): void {
        if ($isDryRun) {
            $this->displayMessageInfo($messageData, $io);
        } else {
            $this->saveMessage($app, $messageData);
        }
    }

    /**
     * @param array<string, mixed> $messageData
     */
    private function displayMessageInfo(array $messageData, SymfonyStyle $io): void
    {
        $messageId = $this->extractStringValue($messageData, 'id', 'unknown');
        $messageQuery = $this->extractStringValue($messageData, 'query', '');
        $messagePreview = substr($messageQuery ?? '', 0, 50);

        $io->text(sprintf('  消息: %s - %s...', $messageId, $messagePreview));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractStringValue(array $data, string $key, ?string $default = null): ?string
    {
        $value = $data[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractIntValue(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchConversations(string $user, ?string $lastId, int $limit): array
    {
        $request = new GetConversationsRequest($user, $lastId, $limit);
        $response = $this->difyApiClient->request($request);

        if (!is_object($response) || !method_exists($response, 'toArray')) {
            throw new \RuntimeException('Response does not support toArray method');
        }

        $result = $response->toArray();
        if (!is_array($result)) {
            throw new \RuntimeException('Response toArray() did not return an array');
        }

        /** @var array<string, mixed> $result */
        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchMessages(string $conversationId, string $user): array
    {
        $request = new GetMessagesRequest($conversationId, $user, null, 1000);
        $response = $this->difyApiClient->request($request);

        if (!is_object($response) || !method_exists($response, 'toArray')) {
            throw new \RuntimeException('Response does not support toArray method');
        }

        $result = $response->toArray();
        if (!is_array($result)) {
            throw new \RuntimeException('Response toArray() did not return an array');
        }

        /** @var array<string, mixed> $result */
        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function saveConversation(DifyApp $app, array $data): void
    {
        $conversationId = $this->extractStringValue($data, 'id');
        if (null === $conversationId) {
            return;
        }

        $conversation = $this->findOrCreateConversation($app, $conversationId);
        $this->mapConversationData($app, $conversation, $data);
        $this->entityManager->persist($conversation);
    }

    private function findOrCreateConversation(DifyApp $app, string $conversationId): Conversation
    {
        $conversation = $this->conversationRepository
            ->findByDifyConversationId($conversationId)
        ;

        if (null === $conversation) {
            $conversation = new Conversation();
            $conversation->setDifyConversationId($conversationId);
            $conversation->setDifyApp($app);

            // 如果API返回中有user字段，使用真实数据；否则使用appId作为默认值
            $userDefault = $app->getId() ?? '';
            $conversation->setUser($userDefault);
        }

        return $conversation;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapConversationData(DifyApp $app, Conversation $conversation, array $data): void
    {
        $conversation->setName($this->extractStringValue($data, 'name', '') ?? '');
        $conversation->setStatus($this->extractStringValue($data, 'status', '') ?? '');
        $conversation->setIntroduction($this->extractStringValue($data, 'introduction'));

        $inputs = $data['inputs'] ?? null;
        /** @var array<string, mixed>|null $inputs */
        $conversation->setInputs(is_array($inputs) ? $inputs : null);

        $conversation->setDifyCreatedAt($this->extractIntValue($data, 'created_at', 0));
        $conversation->setDifyUpdatedAt($this->extractIntValue($data, 'updated_at', 0));

        // 如果API返回中有user字段，更新用户信息
        $user = $this->extractStringValue($data, 'user');
        if (null !== $user) {
            $conversation->setUser($user);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function saveMessage(DifyApp $app, array $data): void
    {
        $messageId = $this->extractStringValue($data, 'id');
        if (null === $messageId) {
            return;
        }

        $message = $this->findOrCreateMessage($app, $messageId);
        $this->mapMessageData($message, $data);
        $this->entityManager->persist($message);
    }

    private function findOrCreateMessage(DifyApp $app, string $messageId): ConversationMessage
    {
        $message = $this->conversationMessageRepository
            ->findByDifyMessageId($messageId)
        ;

        if (null === $message) {
            $message = new ConversationMessage();
            $message->setDifyMessageId($messageId);
            $message->setDifyApp($app);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapMessageData(ConversationMessage $message, array $data): void
    {
        $message->setDifyConversationId($this->extractStringValue($data, 'conversation_id', '') ?? '');
        $message->setQuery($this->extractStringValue($data, 'query', '') ?? '');
        $message->setAnswer($this->extractStringValue($data, 'answer', '') ?? '');
        $message->setUser($this->extractStringValue($data, 'user', '') ?? '');
        $message->setDifyCreatedAt($this->extractIntValue($data, 'created_at', 0));

        $this->setMessageArrayFields($message, $data);
        $this->setMessageFeedback($message, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setMessageArrayFields(ConversationMessage $message, array $data): void
    {
        $inputs = $data['inputs'] ?? null;
        /** @var array<string, mixed>|null $inputs */
        $message->setInputs(is_array($inputs) ? $inputs : null);

        $messageFiles = $data['message_files'] ?? null;
        /** @var array<string, mixed>|null $messageFiles */
        $message->setMessageFiles(is_array($messageFiles) ? $messageFiles : null);

        $retrieverResources = $data['retriever_resources'] ?? null;
        /** @var array<string, mixed>|null $retrieverResources */
        $message->setRetrieverResources(is_array($retrieverResources) ? $retrieverResources : null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setMessageFeedback(ConversationMessage $message, array $data): void
    {
        $feedback = $data['feedback'] ?? [];

        if (!is_array($feedback) || !isset($feedback['rating'])) {
            $message->setFeedbackRating(null);

            return;
        }

        $rating = $feedback['rating'];
        $message->setFeedbackRating(is_string($rating) ? $rating : null);
    }
}
