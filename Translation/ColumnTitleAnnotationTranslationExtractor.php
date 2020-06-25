<?php

namespace APY\DataGridBundle\Translation;

use APY\DataGridBundle\Grid\Mapping\Driver\Annotation;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Node\Node;

class ColumnTitleAnnotationTranslationExtractor implements FileVisitorInterface, \PHPParser_NodeVisitor, ContainerAwareInterface
{
    private $annotated;
    private $catalogue;
    private $parsedClassName;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function beforeTraverse(array $nodes)
    {
        $this->annotated = false;
        $this->parsedClassName = null;
    }

    public function enterNode(\PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
            // Base namespace
            $this->parsedClassName = $node->name->toString();
        } elseif ($node instanceof \PHPParser_Node_Stmt_UseUse) {
            // Don't worry about classes that don't import the grid mapper
            if ('APY_DataGridBundle_Grid_Mapping' == $node->name->toString('_')) {
                $this->annotated = true;
            }
        } elseif ($node instanceof \PHPParser_Node_Stmt_Class) {
            // Append class name to base namespace
            $this->parsedClassName .= '\\' . $node->name;
        }
    }

    public function leaveNode(\PHPParser_Node $node)
    {
    }
    public function afterTraverse(array $nodes)
    {
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->catalogue = $catalogue;

        // Traverse document to assemble class name
        $traverser = new \PHPParser_NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        if ($this->annotated) {
            // Get annotations for the class
            $annotationDriver = new Annotation(new DoctrineAnnotationReader());
            $manager = new Manager($this->container);
            $manager->addDriver($annotationDriver, -1);
            $metadata = $manager->getMetadata($this->parsedClassName);

            // Save messages for title
            foreach ($metadata->getFields() as $field) {
                $mappedField = $metadata->getFieldMapping($field);
                if ((!isset($mappedField['visible']) || $mappedField['visible']) && isset($mappedField['title'])) {
                    $message = new Message($mappedField['title']);
                    $message->addSource(new FileSource((string) $file));
                    $catalogue->add($message);
                }
            }
        }
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, Node $node)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
