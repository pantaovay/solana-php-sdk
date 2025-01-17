<?php

namespace Tighten\SolanaPhpSdk;

use Tighten\SolanaPhpSdk\Exceptions\AccountNotFoundException;
use Tighten\SolanaPhpSdk\Util\Commitment;

class Connection extends Program
{
    public function airdrop(string $pubkey, int $lamports)
    {
        return $this->client->call('requestAirdrop', [$pubkey, $lamports]);
    }

    /**
     * @param string $pubKey
     * @return array
     */
    public function getAccountInfo(string $pubKey): array
    {
        $accountResponse = $this->client->call(
            'getAccountInfo',
            [$pubKey, ['encoding' => 'jsonParsed', 'commitment' => 'confirmed']]
        )['value'];

        if (! $accountResponse) {
            throw new AccountNotFoundException("API Error: Account {$pubKey} not found.");
        }

        return $accountResponse;
    }

    /**
     * @param string $pubKey
     * @return float
     */
    public function getBalance(string $pubKey): float
    {
        return $this->client->call('getBalance', [$pubKey])['value'];
    }

    /**
     * @param string $transactionSignature
     * @return array
     */
    public function getConfirmedTransaction(string $transactionSignature): array
    {
        return $this->client->call('getTransaction', [
            $transactionSignature,
            ['commitment' => Commitment::CONFIRMED]
        ]);
    }

    /**
     * NEW: This method is only available in solana-core v1.7 or newer. Please use getConfirmedTransaction for solana-core v1.6
     *
     * @param string $transactionSignature
     * @return array
     */
    public function getTransaction(string $transactionSignature): array
    {
        return $this->client->call('getTransaction', [$transactionSignature]);
    }

    /**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getRecentBlockhash(?Commitment $commitment = null): array
    {
        return $this->client->call('getRecentBlockhash', array_filter([$commitment]))['value'];
    }

    public function getMinimumBalanceForRentExemption(int $dataLength): int
    {
        return $this->client->call('getMinimumBalanceForRentExemption', [$dataLength]);
    }

    /**
     * @param Transaction $transaction
     * @param Keypair[] $signers
     * @param array $params
     * @return array|\Illuminate\Http\Client\Response
     * @throws Exceptions\GenericException
     * @throws Exceptions\InvalidIdResponseException
     * @throws Exceptions\MethodNotFoundException
     */
    public function sendTransaction(Transaction $transaction, array $signers, array $params = [])
    {
        if (! $transaction->recentBlockhash) {
            $transaction->recentBlockhash = $this->getRecentBlockhash()['blockhash'];
        }

        // make sure no duplicated signers
        $uniqueSigners = [];
        foreach ($signers as $signer) {
            if (isset($uniqueSigners[$signer->getPublicKey()->toBase58()])) {
                continue;
            }

            $uniqueSigners[$signer->getPublicKey()->toBase58()] = $signer;
        }

        $transaction->sign(...array_values($uniqueSigners));

        $rawBinaryString = $transaction->serialize(false);

        $hashString = sodium_bin2base64($rawBinaryString, SODIUM_BASE64_VARIANT_ORIGINAL);

        return $this->client->call('sendTransaction', [
            $hashString,
            [
                'encoding' => 'base64',
                'preflightCommitment' => 'confirmed',
            ],
        ]);
    }
}
