<?php

namespace APY\DataGridBundle\Translation;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;

use APY\DataGridBundle\Grid\Mapping\Driver\Annotation;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Mapping\Source;
use APY\DataGridBundle\Grid\Mapping\Column;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;

class ColumnTitleAnnotationTranslationExtractor implements FileVisitorInterface, \PHPParser_NodeVisitor
{
    private $annotated;
    private $catalogue;
    private $parsedClassName;

    public function beforeTraverse(array $nodes) {
        $this->annotated = false;
        $this->parsedClassName = null;
    }

    public function enterNode(\PHPParser_Node $node) {
        if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
            // Base namespace
            $this->parsedClassName = $node->name->toString();
        }
        elseif ($node instanceof \PHPParser_Node_Stmt_UseUse) {
            // Don't worry about classes that don't import the grid mapper
            if ('APY_DataGridBundle_Grid_Mapping' == $node->name->toString('_')) {
                $this->annotated = true;
            }
        }
        elseif ($node instanceof \PHPParser_Node_Stmt_Class) {
            // Append class name to base namespace
            $this->parsedClassName .= '\\' . $node->name;
        }
    }

    public function leaveNode(\PHPParser_Node $node) { }
    public function afterTraverse(array $nodes) { }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue) { }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast) {
        $this->catalogue = $catalogue;

        // Traverse document to assemble class name
        $traverser = new \PHPParser_NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        if ($this->annotated) {
            // Get annotations for the class
            $annotationDriver = new Annotation(new DoctrineAnnotationReader());
            $manager = new Manager();
            $manager->addDriver($annotationDriver, -1);
            
            // Get all available groups
            $groups = $this->extractGroups();
            foreach ($groups as $group) {
                $metadata = $manager->getMetadata($this->parsedClassName, $group);

                // Save messages for title
                foreach ($metadata->getFields() as $field) {
                    $mappedField = $metadata->getFieldMapping($field);
                    if ((! isset($mappedField['visible']) || $mappedField['visible']) && isset($mappedField['title'])) {
                        $message = new Message($mappedField['title']);
                        $message->addSource(new FileSource((string) $file));
                        $catalogue->add($message);
                    }
                }
            }
        }
    }
    
    /**
     * Extract available grid groups in the current class
     * @return array
     */
    protected function extractGroups()
    {
        $reader = new DoctrineAnnotationReader();
        $groups = array('default');
        
        $reflectionCollection = array();

        $reflectionCollection[] = $reflection = new \ReflectionClass($this->parsedClassName);
        while (false !== $reflection = $reflection->getParentClass()) {
            $reflectionCollection[] = $reflection;
        }
        
        while (!empty($reflectionCollection)) {
            $reflection = array_pop($reflectionCollection);

            foreach ($reader->getClassAnnotations($reflection) as $class) {
                if ($class instanceof Source) {
                    $groups = array_merge($groups, $class->getGroups());
                }
            }

            foreach ($reflection->getProperties() as $property) {
                foreach ($reader->getPropertyAnnotations($property) as $class) {
                    if ($class instanceof Column) {
                        $groups = array_merge($groups, $class->getGroups());
                    }
                }
            }
        }
        
        return $groups;
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $node) { }
}