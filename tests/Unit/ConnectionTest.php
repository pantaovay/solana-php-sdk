<?php

namespace Tighten\SolanaPhpSdk\Tests\Unit;

use Tighten\SolanaPhpSdk\Connection;
use Tighten\SolanaPhpSdk\SolanaRpcClient;
use Tighten\SolanaPhpSdk\Tests\TestCase;

class ConnectionTest extends TestCase
{
    /** @test */
    public function it_get_minimum_balance_for_rent_exemption()
    {
        $rpcClient = new SolanaRpcClient(SolanaRpcClient::DEVNET_ENDPOINT);
        $connection = new Connection($rpcClient);

        $this->assertGreaterThan(0, $connection->getMinimumBalanceForRentExemption(0));

        $one = $connection->getMinimumBalanceForRentExemption(1);
        $two = $connection->getMinimumBalanceForRentExemption(2);

        $this->assertGreaterThan($one, $two);
    }
}
