<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN 
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Helper;



use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;

class Data
{

    public $giftcards = array(
        'webshopgiftcard',
        'babygiftcard',
        'boekenbon',
        'erotiekbon',
        'parfumcadeaukaart',
        'yourgift',
        'wijncadeau',
        'gezondheidsbon',
        'fashioncheque',
        'fashiongiftcard',
        'podium',
        'vvvbon',
        'sportenfit',
        'goodcard',
        'nationaletuinbon',
        'nationaleverwencadeaubon',
        'beautyandwellness',
        'fietsenbon',
        'wellnessgiftcard',
        'winkelcheque',
        'givacard'
    );
    public $gateways = array(
        'ideal',
        'dotpay',
        'betaalnaontvangst',
        'einvoice',
        'klarnainvoice',
        'bancontact',
        'visa',
        'eps',
        'ferbuy',
        'mastercard',
        'mspbanktransfer',
        'maestro',
        'paypalmsp',
        'giropay',
        'sofort',
        'directdebit',
        'americanexpress',
        'creditcard',
        'paysafecard',
        'trustpay',
        'kbc',
        'alipay',
        'belfius',
        'ing'
    );
    
    
     /**
     * File extension lock
     */
    const LOCK_EXTENSION = '.lock';
    /**
     * Max execution (locking) time for process (in seconds)
     */
    const MAX_LOCK_TIME = 20;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $lockFilePath;
    /**
     * @var WriteInterface
     */
    private $tmpDirectory;
    /**
     * @var State
     */
    private $state;
    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct()
    {
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $filessystem =  $objectManager->create('Magento\Framework\Filesystem');
        $this->filesystem = $filessystem;
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }
    
    /**
     * @inheritdoc
     */
    public function lockProcess($lockName)
    {
        if ($this->getState()->getMode() == State::MODE_PRODUCTION) {
            return;
        }
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->lockFilePath = $this->getFilePath($lockName);
        while ($this->isProcessLocked()) {
            usleep(1000);
        }
        $this->tmpDirectory->writeFile($this->lockFilePath, time());
    }
    /**
     * @inheritdoc
     * @throws FileSystemException
     */
    public function unlockProcess()
    {
        if ($this->getState()->getMode() == State::MODE_PRODUCTION) {
            return ;
        }
        $this->tmpDirectory->delete($this->lockFilePath);
    }
    /**
     * Check whether generation process has already locked
     *
     * @return bool
     */
    private function isProcessLocked()
    {
        if ($this->tmpDirectory->isExist($this->lockFilePath)) {
            try {
                $lockTime = (int)$this->tmpDirectory->readFile($this->lockFilePath);
                if ((time() - $lockTime) >= self::MAX_LOCK_TIME) {
                    $this->tmpDirectory->delete($this->lockFilePath);
                    return false;
                }
            } catch (FileSystemException $e) {
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * Get name of lock file
     *
     * @param string $name
     * @return string
     */
    private function getFilePath($name)
    {
        return DirectoryList::TMP . DIRECTORY_SEPARATOR . $name . self::LOCK_EXTENSION;
    }
    /**
     * @return State
     * @deprecated
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }
        return $this->state;
    }
    
    
    public function getAllMethods(){
	    $methods = array_merge($this->gateways, $this->giftcards);
	    
	    $all_methods = array();
	    
	    foreach($methods as $key => $method){
		    $all_methods[$method] = $method;
	    }
	    
	    return $all_methods;
    }
    

    public function getPaymentType($code)
    {
        if (in_array($code, $this->gateways)) {
            return 'gateways';
        } elseif (in_array($code, $this->giftcards)) {
            return 'giftcards';
        }
    }

}
