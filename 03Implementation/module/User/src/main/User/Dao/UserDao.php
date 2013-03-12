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
namespace User\Dao;

use Momoku\Dao\AbstractDao;
use User\Entity\User;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @KeyEntity(key='id', entity='User\\Entity\\User')
 */
class UserDao extends AbstractDao
{

    /**
     * @param string $email
     * @return bool
     */
    public function isEmailExist($email){
        $dql = 'SELECT u FROM User\Entity\User u WHERE u.mail = :mail ';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('mail', $email);
        if($query->getOneOrNullResult() instanceof User){
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isNameExist($name){
        $dql = 'SELECT u FROM User\Entity\User u where u.name = :name ';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('name', $name);
        if($query->getOneOrNullResult() instanceof User){
            return true;
        }
        return false;
    }

    /**
     * @param $nameOrMail
     * @return \User\Entity\User
     */
    public function findByNameOrMail($nameOrMail){
        $dql = 'SELECT u FROM User\Entity\User u where u.name = :nameOrMail OR u.mail = :nameOrMail ';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('nameOrMail', $nameOrMail);
        return $query->getSingleResult();
    }
}
