<?php

namespace Tighten\SolanaPhpSdk;

use Tighten\SolanaPhpSdk\Util\AccountMeta;
use Tighten\SolanaPhpSdk\Util\Buffer;

class TransactionInstruction
{
    /**
     * @var array<AccountMeta>
     */
    public array $keys;
    public PublicKey $programId;
    public Buffer $data;

    public function __construct(PublicKey $programId, array $keys, $data = null)
    {
        $this->programId = $programId;
        $this->keys = $keys;
        $this->data = Buffer::from($data);
    }

    public function toArray(): array
    {
        $keysArray = [];
        foreach ($this->keys as $key) {
            $keysArray[] = $key->toArray();
        }

        return [
            'programId' => $this->programId->toBase58(),
            'keys' => $keysArray,
            'data' => $this->data->toBase58String(),
        ];
    }
}
