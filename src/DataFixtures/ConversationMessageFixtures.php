<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyChatflowBundle\Entity\ConversationMessage;
use Tourze\DifyCoreBundle\DataFixtures\DifyAppFixtures;
use Tourze\DifyCoreBundle\Entity\DifyApp;

/**
 * Dify会话消息数据填充器
 */
final class ConversationMessageFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const MESSAGE_1_REFERENCE = 'message-1';
    public const MESSAGE_2_REFERENCE = 'message-2';
    public const MESSAGE_3_REFERENCE = 'message-3';
    public const MESSAGE_4_REFERENCE = 'message-4';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 获取相关引用
        $difyApp = $this->getReference(DifyAppFixtures::DIFY_APP_DEFAULT_REFERENCE, DifyApp::class);
        $conversation1 = $this->getReference(ConversationFixtures::CONVERSATION_1_REFERENCE, Conversation::class);
        $conversation2 = $this->getReference(ConversationFixtures::CONVERSATION_2_REFERENCE, Conversation::class);
        $conversation3 = $this->getReference(ConversationFixtures::CONVERSATION_3_REFERENCE, Conversation::class);

        $messages = [
            [
                'difyMessageId' => 'msg-a1b2c3d4-e5f6-7890-abcd-111111111111',
                'conversation' => $conversation1,
                'inputs' => ['user_name' => '张三'],
                'query' => '请介绍一下你们公司的主要产品',
                'answer' => '我们公司主要提供AI驱动的智能客服解决方案，包括自然语言处理、语音识别和智能对话系统。我们的产品可以帮助企业提升客户服务效率，降低人工成本，同时提供24/7的全天候服务。您想了解哪个具体产品的详细信息吗？',
                'feedbackRating' => 'like',
                'messageFiles' => null,
                'retrieverResources' => [
                    'documents' => [['type' => 'document', 'id' => 'doc_001', 'title' => '产品介绍文档']],
                    'knowledge' => [['type' => 'knowledge', 'id' => 'kb_001', 'title' => '企业知识库']],
                ],
                'user' => 'test_user_001',
                'reference' => self::MESSAGE_1_REFERENCE,
            ],
            [
                'difyMessageId' => 'msg-b2c3d4e5-f6g7-8901-bcde-222222222222',
                'conversation' => $conversation1,
                'inputs' => ['user_name' => '张三'],
                'query' => '你们的价格怎么样？',
                'answer' => '我们提供灵活的定价方案，根据您的具体需求和使用规模来定制。我们有基础版、专业版和企业版三种套餐。基础版适合小型企业，月费约300元；专业版适合中型企业，月费约800元；企业版支持大规模部署，提供定制化服务。您可以先试用我们的产品，满意后再选择合适的套餐。',
                'feedbackRating' => null,
                'messageFiles' => [
                    'files' => [['type' => 'pdf', 'url' => '/files/pricing.pdf', 'name' => '价格表.pdf']],
                ],
                'retrieverResources' => null,
                'user' => 'test_user_001',
                'reference' => self::MESSAGE_2_REFERENCE,
            ],
            [
                'difyMessageId' => 'msg-c3d4e5f6-g7h8-9012-cdef-333333333333',
                'conversation' => $conversation2,
                'inputs' => ['user_name' => '李四', 'issue_type' => '技术问题'],
                'query' => '我在集成API时遇到了认证失败的问题',
                'answer' => '认证失败通常有几种可能原因：1. API密钥错误或已过期；2. 请求头格式不正确；3. 权限配置问题。请检查以下几点：确保API密钥正确且有效，请求头中包含Authorization: Bearer {your-api-key}，确认您的账户有相应的API调用权限。如果问题仍然存在，请提供具体的错误信息，我会进一步帮您排查。',
                'feedbackRating' => 'like',
                'messageFiles' => null,
                'retrieverResources' => [
                    'documents' => [['type' => 'document', 'id' => 'doc_tech_001', 'title' => 'API集成指南']],
                    'faq' => [['type' => 'faq', 'id' => 'faq_001', 'title' => '常见问题解答']],
                ],
                'user' => 'test_user_002',
                'reference' => self::MESSAGE_3_REFERENCE,
            ],
            [
                'difyMessageId' => 'msg-d4e5f6g7-h8i9-0123-defg-444444444444',
                'conversation' => $conversation3,
                'inputs' => null,
                'query' => '你好',
                'answer' => '您好！欢迎使用我们的AI助手服务。我是您的专属智能客服，可以为您提供产品咨询、技术支持、售后服务等帮助。请问今天有什么我可以为您做的吗？',
                'feedbackRating' => null,
                'messageFiles' => null,
                'retrieverResources' => null,
                'user' => 'test_user_003',
                'reference' => self::MESSAGE_4_REFERENCE,
            ],
        ];

        foreach ($messages as $messageData) {
            $message = new ConversationMessage();
            $message->setDifyMessageId($messageData['difyMessageId']);
            $message->setDifyConversationId($messageData['conversation']->getDifyConversationId());
            $message->setDifyApp($difyApp);
            $message->setInputs($messageData['inputs']);
            $message->setQuery($messageData['query']);
            $message->setAnswer($messageData['answer']);
            $message->setFeedbackRating($messageData['feedbackRating']);
            $message->setMessageFiles($messageData['messageFiles']);
            $message->setRetrieverResources($messageData['retrieverResources']);
            $message->setUser($messageData['user']);

            // 设置Dify创建时间（基于对话的创建时间稍后一些）
            $conversationCreatedAt = $messageData['conversation']->getDifyCreatedAt();
            $message->setDifyCreatedAt($conversationCreatedAt + $faker->numberBetween(60, 3600));

            $manager->persist($message);
            $this->addReference($messageData['reference'], $message);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ConversationFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['DifyChatflowBundle'];
    }
}
