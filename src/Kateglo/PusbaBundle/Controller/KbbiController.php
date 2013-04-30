<?php

namespace Kateglo\PusbaBundle\Controller;

use Kateglo\PusbaBundle\Repository\EntryListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Head;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

class KbbiController extends Controller
{
    /**
     * @var \Kateglo\PusbaBundle\Repository\EntryListRepository
     */
    private $listRepository;

    /**
     * @param EntryListRepository $listRepository
     * @InjectParams({
     *  "listRepository" = @Inject("kateglo.pusba_bundle.repository.entry_list_repository")
     * })
     */
    public function __construct(EntryListRepository $listRepository){
        $this->listRepository = $listRepository;
    }

    /**
     * @Get("/kbbi/")
     * @Head("/kbbi/")
     * @View()
     */
    public function indexAction()
    {
        $offset = $this->getRequest()->get('offset');
        $limit = $this->getRequest()->get('limit');
        return array('entries' => $this->listRepository->getFoundEntry($offset, $limit));
    }
}
