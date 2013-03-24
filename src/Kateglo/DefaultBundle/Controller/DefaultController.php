<?php

namespace Kateglo\DefaultBundle\Controller;

use Kateglo\DefaultBundle\Service\BaseLink;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation\InjectParams;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\BrowserKit\Response;

class DefaultController extends Controller
{
    /**
     * @var BaseLink
     */
    private $baseLink;

    /**
     * @param BaseLink $baseLink
     * @InjectParams
     */
    public function __construct(BaseLink $baseLink){
        $this->baseLink = $baseLink;
    }
    
    /**
     * @Get("/")
     * @View()
     */
    public function indexAction()
    {
        $response = $this->baseLink->get($this);
        $response['form'] = array(
            'search' => array(
                'name' => 'search',
                'action' => 'entri.html',
                'method' => 'get',
                'field' => array(
                    array(
                        'name' => 'query',
                        'type' => 'search'
                    ),
                ),
            )
        );

        return $response;
    }

}
