<?php
/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sorien\DataGridBundle\Grid\Form;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

class GridTypeGuesser implements FormTypeGuesserInterface
{
   protected $reader;

   public function __construct($reader)
   {
       $this->reader = $reader;
   }

   public function guessType($class, $property)
   {
   }

   public function guessRequired($class, $property)
   {
   }

   public function guessMaxLength($class, $property)
   {
   }

   public function guessMinLength($class, $property)
   {
   }

   protected function isMappedClass($class)
   {
       return ;
   }
}
