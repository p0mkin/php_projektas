<?php
class PasswordGenerator
{
    private const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const NUMBERS = '0123456789';
    private const SPECIALS = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    private int $lowercase;
    private int $uppercase;
    private int $numbers;
    private int $specials;
    public function __construct(
        int $lowercase = 2,
        int $uppercase = 2,
        int $numbers = 2,
        int $specials = 2
    )
    {
        $this->lowercase = $lowercase;
        $this->uppercase = $uppercase;
        $this->numbers = $numbers;
        $this->specials = $specials;
    }
    private function pickRandom(string $pool, int $count): array
    {
        $result = [];
        $max = strlen($pool) - 1;

        for ($i = 0; $i < $count; $i++) {
            $result[] = $pool[random_int(0, $max)];
        }

        return $result;
    }
    public function generate(): string
    {
        $chars = array_merge(
            $this->pickRandom(self::LOWERCASE, $this->lowercase),
            $this->pickRandom(self::UPPERCASE, $this->uppercase),
            $this->pickRandom(self::NUMBERS, $this->numbers),
            $this->pickRandom(self::SPECIALS, $this->specials)
        );
        shuffle($chars);
        return implode('', $chars);
    }
    public function getLength(): int
    {
        return $this->lowercase + $this->uppercase
            + $this->numbers + $this->specials;
    }
}