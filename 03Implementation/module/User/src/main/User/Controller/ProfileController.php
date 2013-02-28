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
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use User\Form\ProfileForm;
use User\Form\ChangePasswordForm;
use Momoku\Form\Annotation\AnnotationBuilder;
use Kateglo\Dao\UserDao;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class ProfileController extends AbstractActionController
{
    /**
     * @var \Kateglo\Dao\UserDao
     */
    protected $userDao;

    /**
     * @var \Zend\Authentication\AuthenticationService;
     */
    protected $authService;

    /**
     * @var \Zend\Form\Form
     */
    protected $profileForm;

    /**
     * @var \Zend\Form\Form
     */
    protected $changePasswordForm;

    /**
     * @Inject
     * @param UserDao $userDao
     * @param \User\Form\ProfileForm $profileForm
     * @param \User\Form\ChangePasswordForm $changePasswordForm
     * @param \Momoku\Form\Annotation\AnnotationBuilder $annotationBuilder
     * @param \Zend\Authentication\AuthenticationService $authService
     */
    public function __construct(UserDao $userDao, ProfileForm $profileForm,
                                ChangePasswordForm $changePasswordForm,
                                AnnotationBuilder $annotationBuilder,
                                AuthenticationService $authService)
    {
        $this->authService = $authService;
        $this->userDao = $userDao;
        $this->profileForm = $annotationBuilder->createForm($profileForm);
        $this->changePasswordForm = $annotationBuilder->createForm($changePasswordForm);

    }

    public function indexAction()
    {
        if (!$this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('controller' => 'login', 'action' => 'index'));
        }
        /**@var $request \Zend\Http\PhpEnvironment\Request */
        /**@var $user \Kateglo\Entity\User */
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('name') !== null && $request->getPost('email')) {
                $this->profileForm->setData($request->getPost());
                if ($this->profileForm->isValid()) {
                    $user = $this->authService->getIdentity();
                    $user = $this->userDao->merge($user);
                    $user->setMail($this->profileForm->getData()['email']);
                    $user->setName($this->profileForm->getData()['name']);
                    $this->userDao->persist($user);
                    $this->userDao->flush();
                    new ViewModel(array('profileForm' => $this->profileForm,
                                    'changePasswordForm' => $this->changePasswordForm,
                                    'profileMessages' => 'Profile edited successfully'));
                }
            } else if ($request->getPost('password-old') !== null &&
                $request->getPost('password') !== null &&
                $request->getPost('password-retype') !== null
            ) {
                $this->changePasswordForm->setData($request->getPost());
                if($this->changePasswordForm->isValid()){
                    $user = $this->authService->getIdentity();
                    $user = $this->userDao->merge($user);
                    $user->setPassword(md5($this->profileForm->getData()['password']));
                    $this->userDao->persist($user);
                    $this->userDao->flush();
                    new ViewModel(array('profileForm' => $this->profileForm,
                        'changePasswordForm' => $this->changePasswordForm,
                        'changePasswordMessages' => 'Password changed successfully'));
                }
            }
        }

        return new ViewModel(array('profileForm' => $this->profileForm, 'changePasswordForm' => $this->changePasswordForm));
    }
}
