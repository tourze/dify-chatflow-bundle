<?php

declare(strict_types=1);

namespace Tourze\DifyChatflowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Tourze\DifyChatflowBundle\Entity\Conversation;
use Tourze\DifyCoreBundle\DataFixtures\DifyAppFixtures;
use Tourze\DifyCoreBundle\Entity\DifyApp;

/**
 * Dify会话数据填充器
 */
final class ConversationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const CONVERSATION_1_REFERENCE = 'conversation-1';
    public const CONVERSATION_2_REFERENCE = 'conversation-2';
    public const CONVERSATION_3_REFERENCE = 'conversation-3';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 获取 DifyApp 引用
        $difyApp = $this->getReference(DifyAppFixtures::DIFY_APP_DEFAULT_REFERENCE, DifyApp::class);

        $conversations = [
            [
                'difyConversationId' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'name' => '产品咨询对话',
                'inputs' => ['user_name' => '张三', 'topic' => '产品介绍'],
                'status' => 'active',
                'introduction' => '您好！我是AI助手，很高兴为您介绍我们的产品。请问您想了解哪方面的信息？',
                'user' => 'test_user_001',
                'reference' => self::CONVERSATION_1_REFERENCE,
            ],
            [
                'difyConversationId' => 'b2c3d4e5-f6g7-8901-bcde-f23456789012',
                'name' => '技术支持对话',
                'inputs' => ['user_name' => '李四', 'issue_type' => '技术问题'],
                'status' => 'active',
                'introduction' => '欢迎使用技术支持服务！请详细描述您遇到的问题，我会尽力帮助您解决。',
                'user' => 'test_user_002',
                'reference' => self::CONVERSATION_2_REFERENCE,
            ],
            [
                'difyConversationId' => 'c3d4e5f6-g7h8-9012-cdef-345678901234',
                'name' => '通用咨询对话',
                'inputs' => null,
                'status' => 'completed',
                'introduction' => null,
                'user' => 'test_user_003',
                'reference' => self::CONVERSATION_3_REFERENCE,
            ],
        ];

        foreach ($conversations as $conversationData) {
            $conversation = new Conversation();
            $conversation->setDifyConversationId($conversationData['difyConversationId']);
            $conversation->setDifyApp($difyApp);
            $conversation->setName($conversationData['name']);
            $conversation->setInputs($conversationData['inputs']);
            $conversation->setStatus($conversationData['status']);
            $conversation->setIntroduction($conversationData['introduction']);
            $conversation->setUser($conversationData['user']);

            // 设置时间戳
            $createdAt = $faker->unixTime;
            $conversation->setDifyCreatedAt($createdAt);
            $conversation->setDifyUpdatedAt($createdAt + $faker->numberBetween(0, 86400));

            $manager->persist($conversation);
            $this->addReference($conversationData['reference'], $conversation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DifyAppFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['DifyChatflowBundle'];
    }
}
