<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Console;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommandStyle
 */
class CommandStyle extends SymfonyStyle
{
    private $_input;
    private $_output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $this->_input = $input;
        $this->_output = $output;
    }

    public function getInput()
    {
        return $this->_input;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function askAutocomplete($question, array $suggestions, $validate = true)
    {
        $question = new Question($question);
        $question->setAutocompleterValues($suggestions);
        if ($validate === true) {
            $question->setValidator(function($value) use($suggestions) {
                if (!in_array($value, $suggestions)) {
                    throw new \InvalidArgumentException('Invalid value');
                }
                return $value;
            });
        } else if ($validate !== false) {
            $question->setValidator(function($value) use ($validate){
                $value = trim((string) $value);
                if (!preg_match($validate, $value)) {
                    throw new \InvalidArgumentException('Invalid value');
                }
                return $value;
            });
        }

        return $this->askQuestion($question);
    }

    public function askNotEmpty(string $question, $defaultValue = null): string
    {
        return $this->ask($question, $defaultValue, function($value){
            $value = trim((string) $value);
            if (!strlen($value)) {
                throw new \InvalidArgumentException('Value can not be empty');
            }
            return $value;
        });
    }

    public function askValidate(string $question, string $regex): string
    {
        return $this->ask($question, null, function($value) use ($regex){
            $value = trim((string) $value);
            if (!preg_match($regex, $value)) {
                throw new \InvalidArgumentException('Invalid value');
            }
            return $value;
        });
    }
}
