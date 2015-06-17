<?php

/**
 * This file is part of the JoliNotif project.
 *
 * (c) Loïck Piera <pyrech@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joli\JoliNotif\tests\Notifier;

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\Util\OsHelper;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Classes using this trait should define a BINARY constant and extend
 * NotifierTestCase.
 */
trait CliBasedNotifierTestTrait
{
    public function testIsSupported()
    {
        if (OsHelper::isUnix()) {
            $commandLine = 'command -v '.static::BINARY.' >/dev/null 2>&1';
        } else {
            $commandLine = 'where '.static::BINARY;
        }

        passthru($commandLine, $return);
        $supported = 0 === $return;

        $this->assertEquals($supported, $this->getNotifier()->isSupported());
    }

    protected function assertCommandLine($expectedCommandLine, $notification)
    {
        $processBuilder = new ProcessBuilder();
        $processBuilder->setPrefix(self::BINARY);

        $this->invokeMethod($this->getNotifier(), 'configureProcess', [$processBuilder, $notification]);

        $this->assertEquals($expectedCommandLine, $processBuilder->getProcess()->getCommandLine());
    }

    public function testSendThrowsExceptionWhenNotificationDoesntHaveBody()
    {
        $notifier = $this->getNotifier();

        $notification = new Notification();

        try {
            $notifier->send($notification);
            $this->fail('Expected a InvalidNotificationException');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Joli\JoliNotif\Exception\InvalidNotificationException', $e);
        }
    }

    public function testSendThrowsExceptionWhenNotificationHasAnEmptyBody()
    {
        $notifier = $this->getNotifier();

        $notification = (new Notification())
            ->setBody('')
        ;

        try {
            $notifier->send($notification);
            $this->fail('Expected a InvalidNotificationException');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Joli\JoliNotif\Exception\InvalidNotificationException', $e);
        }
    }

    public function testConfigureProcessAcceptNotificationWithOnlyABody()
    {
        $notification = (new Notification())
            ->setBody('I\'m the notification body')
        ;

        $this->assertCommandLine(
            $this->getExpectedCommandLineForNotification(),
            $notification
        );
    }

    public function testConfigureProcessAcceptNotificationWithABodyAndATitle()
    {
        $notification = (new Notification())
            ->setBody('I\'m the notification body')
            ->setTitle('I\'m the notification title')
        ;

        $this->assertCommandLine(
            $this->getExpectedCommandLineForNotificationWithATitle(),
            $notification
        );
    }

    public function testConfigureProcessAcceptNotificationWithABodyAndAnIcon()
    {
        $notification = (new Notification())
            ->setBody('I\'m the notification body')
            ->setIcon('/home/toto/Images/my-icon.png')
        ;

        $this->assertCommandLine(
            $this->getExpectedCommandLineForNotificationWithAnIcon(),
            $notification
        );
    }

    public function testConfigureProcessAcceptNotificationWithAllOptions()
    {
        $notification = (new Notification())
            ->setBody('I\'m the notification body')
            ->setTitle('I\'m the notification title')
            ->setIcon('/home/toto/Images/my-icon.png')
        ;

        $this->assertCommandLine(
            $this->getExpectedCommandLineForNotificationWithAllOptions(),
            $notification
        );
    }

    /**
     * @return string
     */
    abstract protected function getExpectedCommandLineForNotification();

    /**
     * @return string
     */
    abstract protected function getExpectedCommandLineForNotificationWithATitle();

    /**
     * @return string
     */
    abstract protected function getExpectedCommandLineForNotificationWithAnIcon();

    /**
     * @return string
     */
    abstract protected function getExpectedCommandLineForNotificationWithAllOptions();
}
