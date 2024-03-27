<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class XMLExport extends Export
{
    protected ?string $fileExtension = 'xml';

    protected string $mimeType = 'application/xml';

    public function computeData(GridInterface $grid): void
    {
        $xmlEncoder = new XmlEncoder();
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['xml' => $xmlEncoder]);

        $data = $this->getGridData($grid);

        $convertData['titles'] = $data['titles'];
        $convertData['rows']['row'] = $data['rows'];

        $this->content = $serializer->serialize($convertData, 'xml');
    }
}
