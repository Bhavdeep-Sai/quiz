<?php

namespace App\QuestionTypes;

use App\QuestionTypes\Contracts\QuestionTypeInterface;
use App\QuestionTypes\Types\{
    BooleanType,
    SingleChoiceType,
    MultipleChoiceType,
    NumberType,
    TextType,
};
use InvalidArgumentException;

/**
 * Question Type Resolver
 * 
 * Maps question type identifiers to their handler classes
 * This is the Strategy Pattern implementation - NO switch/if statements!
 * 
 * To add a new question type:
 * 1. Create a class extending BaseQuestionType
 * 2. Register it in the TYPE_MAP
 * Done! No other code changes needed.
 */
class QuestionTypeResolver
{
    /**
     * Mapping of type identifiers to handler classes
     * 
     * This is where you register new question types
     * It's a simple associative array - no logic needed
     */
    private static array $typeMap = [
        'boolean' => BooleanType::class,
        'single_choice' => SingleChoiceType::class,
        'multiple_choice' => MultipleChoiceType::class,
        'number' => NumberType::class,
        'text' => TextType::class,
    ];

    /**
     * Resolve a question type handler
     * 
     * @param string $type The question type identifier
     * @return QuestionTypeInterface The handler instance
     * @throws InvalidArgumentException If type is not registered
     */
    public static function resolve(string $type): QuestionTypeInterface
    {
        if (!isset(self::$typeMap[$type])) {
            throw new InvalidArgumentException(
                "Question type '{$type}' is not registered. Available types: " . 
                implode(', ', array_keys(self::$typeMap))
            );
        }

        $handlerClass = self::$typeMap[$type];
        return new $handlerClass();
    }

    /**
     * Get all registered question types
     * 
     * @return array List of type identifiers and their display names
     */
    public static function getAvailableTypes(): array
    {
        return [
            'boolean' => [
                'name' => 'True/False',
                'description' => 'Simple true or false question',
                'icon' => '📋',
            ],
            'single_choice' => [
                'name' => 'Single Choice',
                'description' => 'Select one correct answer from multiple options',
                'icon' => '⭕',
            ],
            'multiple_choice' => [
                'name' => 'Multiple Choice',
                'description' => 'Select one or more correct answers (supports partial scoring)',
                'icon' => '✓',
            ],
            'number' => [
                'name' => 'Number',
                'description' => 'Enter a numeric value (supports tolerance/range)',
                'icon' => '🔢',
            ],
            'text' => [
                'name' => 'Text',
                'description' => 'Free-form text answer (auto or manual grading)',
                'icon' => '📝',
            ],
        ];
    }

    /**
     * Check if a type is registered
     * 
     * @param string $type The type to check
     * @return bool True if type exists, false otherwise
     */
    public static function isValid(string $type): bool
    {
        return isset(self::$typeMap[$type]);
    }

    /**
     * Get all type identifiers
     * 
     * @return array List of all available type identifiers
     */
    public static function getAllTypes(): array
    {
        return array_keys(self::$typeMap);
    }

    /**
     * Register a new question type handler
     * 
     * Allows runtime registration of custom handlers
     * 
     * @param string $type The type identifier
     * @param string $handlerClass The fully qualified class name
     * @throws InvalidArgumentException If class doesn't implement QuestionTypeInterface
     */
    public static function register(string $type, string $handlerClass): void
    {
        if (!is_subclass_of($handlerClass, QuestionTypeInterface::class)) {
            throw new InvalidArgumentException(
                "Handler class must implement QuestionTypeInterface"
            );
        }

        self::$typeMap[$type] = $handlerClass;
    }
}
