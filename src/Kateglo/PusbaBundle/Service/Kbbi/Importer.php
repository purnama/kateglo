<?php
/**
 *  Kateglo: Kamus, Tesaurus dan Glosarium bahasa Indonesia.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the GPL 2.0. For more information, see
 * <http://code.google.com/p/kateglo/>.
 *
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link    http://code.google.com/p/kateglo/
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
namespace Kateglo\PusbaBundle\Service\Kbbi;

use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation\Service;
use Kateglo\PusbaBundle\Entity\EntryCrawl;
use Kateglo\PusbaBundle\Entity\EntryCrawlConfig;
use Kateglo\PusbaBundle\Entity\EntryCrawlHistory;
use Kateglo\PusbaBundle\Entity\EntryList;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Kateglo\PusbaBundle\Repository\EntryCrawlRepository;
use Kateglo\PusbaBundle\Repository\EntryListRepository;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiExtractorException;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Importer
{
    /**
     * @var EntryListRepository
     */
    private $listRepository;

    /**
     * @var EntryCrawlRepository
     */
    private $crawlRepository;

    /**
     * @var Requester
     */
    private $requester;

    /**
     * @param EntryListRepository $listRepository
     * @param EntryCrawlRepository $crawlRepository
     * @param Requester $requester
     * @InjectParams({
     *  "listRepository" = @Inject("kateglo.pusba_bundle.repository.entry_list_repository"),
     *  "crawlRepository" = @Inject("kateglo.pusba_bundle.repository.entry_crawl_repository"),
     *  "requester" = @Inject("kateglo.pusba_bundle.service.kbbi.requester")
     * })
     */
    public function __construct(
        EntryListRepository $listRepository,
        EntryCrawlRepository $crawlRepository,
        Requester $requester
    ) {
        $this->listRepository = $listRepository;
        $this->crawlRepository = $crawlRepository;
        $this->requester = $requester;
    }

    /**
     * @param $content
     */
    public function import($content)
    {
        @ini_set('memory_limit', '3069M');
        $entries = explode("\n", $content);
        foreach ($entries as $entry) {
            $entryList = new EntryList();
            $entryList->setEntry($entry);
            $this->listRepository->persist($entryList);
        }
        $this->listRepository->flush();
    }

    public function crawl($limit = 100, $start = false)
    {
        @ini_set('memory_limit', '3069M');
        try {
            $config = $this->crawlRepository->getCrawlConfig();
        } catch (NoResultException $e) {
            $config = new EntryCrawlConfig();
        }
        if ($start) {
            $config->setLastId(0);
            $this->listRepository->reset();
        }
        $entries = $this->listRepository->findAll($config->getLastId(), $limit);
        $this->requester->setOpCode(1);
        $crawlHistory = new EntryCrawlHistory();
        $crawlHistory->setStartId($config->getLastId());
        $crawlHistory->setStartTime(new \DateTime());
        try {
            /** @var $entry EntryList */
            foreach ($entries as $entry) {
                $this->requester->setParam($entry->getEntry());
                try {
                    $wordList = $this->requester->getRawExtracted();
                    foreach ($wordList as $word => $content) {
                        try {
                            $entryCrawl = $this->crawlRepository->findByEntry($word);
                        } catch (NoResultException $e) {
                            $entryCrawl = new EntryCrawl();
                        }
                        $entryCrawl->setEntry($word);
                        $entryCrawl->setRaw($content);
                        $entryCrawl->setList($entry);
                        $entryCrawl->setLastUpdated(new \DateTime());
                        $this->crawlRepository->persist($entryCrawl);
                    }
                    $entry->setFound(true);
                    $this->listRepository->persist($entry);
                    $config->setLastId($entry->getId());
                } catch (KbbiExtractorException $e) {
                    continue;
                }
            }
            $config->setLastUpdated(new \DateTime());
            $this->crawlRepository->persistConfig($config);
            $crawlHistory->setFinishId($entry->getId());
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(true);
            $this->crawlRepository->persistHistory($crawlHistory);
            $this->crawlRepository->flush();
        } catch (\Exception $e) {
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(false);
            $crawlHistory->setMessages($e->getMessage());
            $this->crawlRepository->persistHistory($crawlHistory);
            $this->crawlRepository->flush();
            throw $e;
        }
    }

}
