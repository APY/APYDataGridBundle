<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Form;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

class GridTypeGuesser implements FormTypeGuesserInterface
{
   /**
    * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Manager;
    */
   private $manager;

   /**
    * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
    */
   private $metadata;

   /**
    * @param $manager
    */
   public function __construct($manager)
   {
       $this->manager = $manager;
   }

   public function guessType($class, $property)
   {
        $metadata = $this->manager->getMetadata($class);

        if ($metadata->hasFieldMapping($property))
        {
            switch ($metadata->getFieldMappingType($property))
            {
                case 'select':
                    $params = $metadata->getFieldMapping($property);
                    $values = $params['values'];

                    if (!empty($values))
                    {
                        return new TypeGuess('choice', array('choices' => $values), Guess::HIGH_CONFIDENCE);
                    }
                case 'boolean':
                        return new TypeGuess('choice', array('choices' => array('1'=>'true','0'=>'false')), Guess::HIGH_CONFIDENCE);
                break;
            }
        }
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
