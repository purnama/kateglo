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
namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 *
 * @Entity
 * @Table(name="user")
 */
class User
{

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @var int user id
     */
    protected $id;

    /**
     * @Version
     * @Column(type="integer")
     * @var int version
     */
    protected $version;

    /**
     * @Column(type="string", unique=true, nullable=true)
     * @var string name of the user
     */
    protected $name;

    /**
     * @Column(type="string", unique=true, nullable=true)
     * @var string email address
     */
    protected $mail;

    /**
     * @Column(type="string")
     * @var string user password
     */
    protected $password;

    /**
     * @Column(type="datetime")
     * @var string user since date
     */
    protected $since;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="\User\Entity\Role", inversedBy="users")
     * @JoinTable(name="rel_user_role",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $since
     */
    public function setSince($since)
    {
        $this->since = $since;
    }

    /**
     * @return string
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * @param \User\Entity\Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }
    }

    /**
     * @param \User\Entity\Role $role
     */
    public function removeRole(Role $role)
    {
        $removed = $this->roles->removeElement($role);
        if ($removed !== null) {
            $removed->removeUser($this);
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRoles(){
        return $this->roles;
    }
}
