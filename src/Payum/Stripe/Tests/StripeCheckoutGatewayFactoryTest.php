<?php

namespace Payum\Stripe\Tests;

use Payum\Core\Exception\LogicException;
use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Stripe\StripeCheckoutGatewayFactory;

class StripeCheckoutGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new StripeCheckoutGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions()
    {
        $factory = new StripeCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals([
            'publishable_key' => '',
            'secret_key' => '',
        ], $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new StripeCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('stripe_checkout', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Stripe Checkout', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The publishable_key, secret_key fields are required.');
        $factory = new StripeCheckoutGatewayFactory();

        $factory->create();
    }

    public function testShouldConfigurePaths()
    {
        $factory = new StripeCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('PayumStripe', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumStripe']);
        $this->assertFileExists($config['payum.paths']['PayumStripe']);
    }

    protected function getGatewayFactoryClass(): string
    {
        return StripeCheckoutGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
        ];
    }
}
