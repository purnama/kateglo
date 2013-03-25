<?php

namespace Kateglo\DefaultBundle\Controller;

use Kateglo\DefaultBundle\Service\BaseLink;
use Kateglo\DefaultBundle\Service\GenerateUrlInterface;
use Kateglo\DefaultBundle\ViewModel\Form;
use Kateglo\DefaultBundle\ViewModel\Input;
use Kateglo\DefaultBundle\ViewModel\Start;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Head;
use FOS\RestBundle\Controller\Annotations\Options;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller implements GenerateUrlInterface
{
    /**
     * @var BaseLink
     */
    private $baseLink;

    /**
     * @param BaseLink $baseLink
     * @InjectParams({
     *  "baseLink" = @Inject("kateglo.defaultbundle.service.baselink")
     * })
     */
    public function __construct(BaseLink $baseLink){
        $this->baseLink = $baseLink;
    }
    
    /**
     * @Get("/")
     * @Head("/")
     * @View()
     */
    public function indexAction()
    {
        $base = $this->baseLink->get($this);
        $form = new Form(array(
            'search' => new Input('search', null, 'query')
        ), $this->generateUrl('kateglo_default_default_index'), 'get', 'search');

        return new Start($base, $form);
    }

    /**
     * @Options("/")
     */
    public function indexOptionsAction(){
        return new Response();
    }

}
