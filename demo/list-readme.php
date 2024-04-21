<?php

declare(strict_types=1);
use AdrienBrault\Instructrice\Attribute\Instruction;
use AdrienBrault\Instructrice\Instructrice;
use AdrienBrault\Instructrice\InstructriceFactory;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class Character
{
    #[Instruction('Just the first name.')]
    public string $name;

    #[Instruction('If applicable, the military rank.')]
    public ?string $rank = null;
}

$demo = require __DIR__ . '/bootstrap.php';
$demo(function (Instructrice $instructrice, ?string $context, ConsoleOutputInterface $output) {
    $characters = $instructrice->getList(
        Character::class,
        $context ?? 'Colonel Jack O\'Neil walks into a bar and meets Major Samanta Carter. They call Teal\'c to join them.',
        onChunk: InstructriceFactory::createOnChunkDump($output->section()),
    );
});
