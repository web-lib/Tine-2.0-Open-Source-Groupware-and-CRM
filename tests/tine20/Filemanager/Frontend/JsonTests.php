<?php
/**
 * Tine 2.0 - http://www.tine20.org
 *
 * @package     Filemanager
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2011-2017 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schüle <p.schuele@metaways.de>
 *
 */

/**
 * Test class for Filemanager_Frontend_Json
 *
 * @package     Filemanager
 */
class Filemanager_Frontend_JsonTests extends TestCase
{
    /**
     * @var array test objects
     */
    protected $_objects = array();
    
    /**
     * uit
     *
     * @var Filemanager_Frontend_Json
     */
    protected $_json = null;
    
    /**
     * fs controller
     *
     * @var Tinebase_FileSystem
     */
    protected $_fsController;
    
    /**
     * filemanager app
     *
     * @var Tinebase_Model_Application
     */
    protected $_application;
    
    /**
     * personal container
     *
     * @var Tinebase_Model_Container
     */
    protected $_personalContainer;
    
    /**
     * shared container
     *
     * @var Tinebase_Model_Container
     */
    protected $_sharedContainer;
    
    /**
     * other user container
     *
     * @var Tinebase_Model_Container
     */
    protected $_otherUserContainer;

    /**
     * folder paths to be deleted
     *
     * @var array
     */
    protected $_rmDir = array();
    
    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_fsController = Tinebase_FileSystem::getInstance();
        $this->_application = Tinebase_Application::getInstance()->getApplicationByName('Filemanager');
        $this->_rmDir = array();

        // make sure account root node exists
        $this->_getPersonalFilemanagerContainer();
    }
    
    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (count($this->_rmDir) > 0) {
            foreach ($this->_rmDir as $dir) {
                $this->_getUit()->deleteNodes($dir);
            }
        }
        
        Tinebase_FileSystem::getInstance()->clearStatCache();
        Tinebase_FileSystem::getInstance()->clearDeletedFilesFromFilesystem();
        
        $this->_personalContainer  = null;
        $this->_sharedContainer    = null;
        $this->_otherUserContainer = null;
    }

    /**
     * @return Filemanager_Frontend_Json
     */
    protected function _getUit()
    {
        if ($this->_json === null) {
            $this->_json = new Filemanager_Frontend_Json();
        }
        return $this->_json;
    }

    /**
     * test search nodes (personal)
     */
    public function testSearchRoot()
    {
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => '/'
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->_assertRootNodes($result);
    }
    
    /**
     * assert 3 root nodes
     *
     * @param array $searchResult
     */
    protected function _assertRootNodes($searchResult)
    {
        $translate = Tinebase_Translation::getTranslation('Filemanager');
        $this->assertEquals(3, $searchResult['totalcount'], 'did not get root nodes: ' . print_r($searchResult, true));
        $this->assertEquals(array(
            'id'             => 'myUser',
            'path'           => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' .
                Tinebase_Core::getUser()->accountLoginName,
            'name'           => $translate->_('My folders'),
            'type'           => 'folder',
            'grants'         => array(),
            'account_grants' => array(
                'account_id' => Tinebase_Core::getUser()->getId(),
                'account_type' => Tinebase_Acl_Rights::ACCOUNT_TYPE_USER,
                Tinebase_Model_Grants::GRANT_READ => true,
                Tinebase_Model_Grants::GRANT_ADD => true,
                Tinebase_Model_Grants::GRANT_EDIT => false,
                Tinebase_Model_Grants::GRANT_DELETE => false,
                Tinebase_Model_Grants::GRANT_EXPORT => true,
                Tinebase_Model_Grants::GRANT_SYNC => true,
                Tinebase_Model_Grants::GRANT_ADMIN => false,
                Tinebase_Model_Grants::GRANT_FREEBUSY => false,
                Tinebase_Model_Grants::GRANT_PRIVATE => false,
                Tinebase_Model_Grants::GRANT_DOWNLOAD => false,
                Tinebase_Model_Grants::GRANT_PUBLISH => false,
            ),
            'tags'           => array(),
            'revisionProps'  => array(),
            'notificationProps' => array(),
            ), $searchResult['results'][0], 'my user folder mismatch');
        $this->assertEquals(array(
            'id'    => Tinebase_Model_Container::TYPE_SHARED,
            'path'  => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED,
            'name' => $translate->_('Shared folders'),
            'type' => 'folder',
            'grants'         => array(),
            'account_grants' => array(
                'account_id' => Tinebase_Core::getUser()->getId(),
                'account_type' => Tinebase_Acl_Rights::ACCOUNT_TYPE_USER,
                Tinebase_Model_Grants::GRANT_READ => true,
                Tinebase_Model_Grants::GRANT_ADD => true,
                Tinebase_Model_Grants::GRANT_EDIT => false,
                Tinebase_Model_Grants::GRANT_DELETE => false,
                Tinebase_Model_Grants::GRANT_EXPORT => true,
                Tinebase_Model_Grants::GRANT_SYNC => true,
                Tinebase_Model_Grants::GRANT_ADMIN => false,
                Tinebase_Model_Grants::GRANT_FREEBUSY => false,
                Tinebase_Model_Grants::GRANT_PRIVATE => false,
                Tinebase_Model_Grants::GRANT_DOWNLOAD => false,
                Tinebase_Model_Grants::GRANT_PUBLISH => false,
            ),
            'tags' => array(),
            'revisionProps'  => array(),
            'notificationProps' => array(),
        ), $searchResult['results'][1], 'shared folder mismatch');
        $this->assertEquals(array(
            'id'    => Tinebase_Model_Container::TYPE_OTHERUSERS,
            'path'  => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL,
            'name' => $translate->_('Other users folders'),
            'type' => 'folder',
            'grants'         => array(),
            'account_grants' => array(
                'account_id' => Tinebase_Core::getUser()->getId(),
                'account_type' => Tinebase_Acl_Rights::ACCOUNT_TYPE_USER,
                Tinebase_Model_Grants::GRANT_READ => true,
                Tinebase_Model_Grants::GRANT_ADD => false,
                Tinebase_Model_Grants::GRANT_EDIT => false,
                Tinebase_Model_Grants::GRANT_DELETE => false,
                Tinebase_Model_Grants::GRANT_EXPORT => false,
                Tinebase_Model_Grants::GRANT_SYNC => false,
                Tinebase_Model_Grants::GRANT_ADMIN => false,
                Tinebase_Model_Grants::GRANT_FREEBUSY => false,
                Tinebase_Model_Grants::GRANT_PRIVATE => false,
                Tinebase_Model_Grants::GRANT_DOWNLOAD => false,
                Tinebase_Model_Grants::GRANT_PUBLISH => false,
            ),
            'tags' => array(),
            'revisionProps'  => array(),
            'notificationProps' => array(),
        ), $searchResult['results'][2], 'other user folder mismatch');
    }
    
    /**
     * test search nodes (personal)
     */
    public function testSearchPersonalNodes()
    {
        $this->_setupTestPath(Tinebase_FileSystem::FOLDER_TYPE_PERSONAL);

        $path = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/'
            . Tinebase_Core::getUser()->accountLoginName . '/' . $this->_getPersonalFilemanagerContainer()->name;
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => $path
        ));
        $result = $this->_searchHelper($filter, 'unittestdir_personal');
        // check correct path resolving
        $node = $this->_getNodeByNameFromResult('unittestdir_personal', $result);
        self::assertEquals($path . '/unittestdir_personal', $node['path']);
    }
    
    /**
     * search node helper
     *
     * @param array $_filter
     * @param string $_expectedName
     * @return array search result
     */
    protected function _searchHelper($_filter, $_expectedName, $_toplevel = false, $_checkAccountGrants = true)
    {
        $result = $this->_getUit()->searchNodes($_filter, array('sort' => 'size'));
        
        $this->assertGreaterThanOrEqual(1, $result['totalcount'], 'expected at least one entry');
        $node = $this->_getNodeByNameFromResult($_expectedName, $result);
        if ($_toplevel) {
            $found = false;
            foreach ($result['results'] as $container) {
                // toplevel containers are resolved (array structure below [name])
                if ($_expectedName == $container['name']) {
                    $found = true;
                    if ($_checkAccountGrants) {
                        $this->assertTrue(isset($container['account_grants']));
                        $this->assertEquals(Tinebase_Core::getUser()->getId(),
                            $container['account_grants']['account_id']);
                    }
                }
            }
            $this->assertTrue($found, 'container not found: ' . print_r($result['results'], true));
        } else {
            self::assertNotNull($node);
        }

        if ($_checkAccountGrants) {
            $this->assertTrue(isset($node['account_grants']), 'account grants missing');
            $this->assertEquals(Tinebase_Core::getUser()->getId(), $node['account_grants']['account_id']);
        }
        
        return $result;
    }

    protected function _getNodeByNameFromResult($_expectedName, $_result)
    {
        self::assertTrue(isset($_result['results']));
        foreach ($_result['results'] as $node) {
            if ($node['name'] === $_expectedName) {
                return $node;
            }
        }

        return null;
    }
    
    /**
     * test search nodes (shared)
     */
    public function testSearchSharedNodes()
    {
        $this->_setupTestPath(Tinebase_FileSystem::FOLDER_TYPE_SHARED);
        
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/' . $this->_getSharedContainer()->name
        ));
        $this->_searchHelper($filter, 'unittestdir_shared');
    }
    
    /**
     * test search nodes (other)
     */
    public function testSearchOtherUsersNodes()
    {
        $this->_setupTestPath(Tinebase_Model_Container::TYPE_OTHERUSERS);
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/sclever/' .
                $this->_getOtherUserContainer()->name
        ));
        $this->_searchHelper($filter, 'unittestdir_other');
    }
    
    /**
     * search top level containers of user
     * 
     * @see 0007400: Newly created directories disappear
     */
    public function testSearchTopLevelContainersOfUser()
    {
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName
        ));
        $this->_searchHelper($filter, $this->_getPersonalFilemanagerContainer()->name, true);
        
        $another = $this->testCreateContainerNodeInPersonalFolder();
        $this->_searchHelper($filter, $another['name'], true);
    }

    /**
     * search shared top level containers 
     */
    public function testSearchSharedTopLevelContainers()
    {
        $this->_setupTestPath(Tinebase_FileSystem::FOLDER_TYPE_SHARED);
        
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED
        ));
        $this->_searchHelper($filter, $this->_getSharedContainer()->name, true);
    }

    /**
     * search top level containers of other users
     */
    public function testSearchTopLevelContainersOfOtherUsers()
    {
        $this->_getOtherUserContainer();
        
        $filter = array(
            array(
                'field'    => 'path', 
                'operator' => 'equals', 
                'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL
            )
        );
        $result = $this->_searchHelper(
            $filter,
            $this->_personas['sclever']->accountDisplayName,
            /* $_toplevel */ false,
            /* $_checkAccountGrants */ false
        );
        // make sure, own user is not in other users
        $found = false;
        foreach ($result['results'] as $node) {
            if ($node['path'] === '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName) {
                $found = true;
            }
        }
        self::assertFalse($found, 'own personal node found! ' . print_r($result['results'], true));
        // check correct path resolving
        $node = $this->_getNodeByNameFromResult($this->_personas['sclever']->accountDisplayName, $result);
        $path = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . $this->_personas['sclever']->accountDisplayName;
        self::assertEquals($path, $node['path']);
        self::assertEquals(1, $result['totalcount'],
            'only expected sclever personal folder in result. got: ' . print_r($result['results'], true));
    }

    /**
     * search containers of other user
     */
    public function testSearchContainersOfOtherUser()
    {
        $this->_setupTestPath(Tinebase_Model_Container::TYPE_OTHERUSERS);
        
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/sclever'
        ), array(
            'field'    => 'creation_time', 
            'operator' => 'within', 
            'value'    => 'weekThis',
        ));
        $result = $this->_searchHelper($filter, $this->_getOtherUserContainer()->name, true);

        $expectedPath = $filter[0]['value'] . '/' . $this->_getOtherUserContainer()->name;
        $node = $this->_getNodeByNameFromResult($this->_getOtherUserContainer()->name, $result);
        self::assertNotNull($node);
        self::assertEquals($expectedPath, $node['path'], 'node path mismatch');
        self::assertEquals($filter[0]['value'], $result['filter'][0]['value']['path'], 'filter path mismatch');
    }

    /**
     * testSearchWithInvalidPath
     * 
     * @see 0007110: don't show exception for invalid path filters
     */
    public function testSearchWithInvalidPath()
    {
        // wrong user
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/xyz'
        ));
        
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->_assertRootNodes($result);
        
        // wrong type
        $filter[0]['value'] = '/lala';
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->_assertRootNodes($result);

        // no path filter
        $result = $this->_getUit()->searchNodes(array(), array());
        $this->_assertRootNodes($result);
    }
    
    /**
     * create container in personal folder
     *
     * @return array created node
     */
    public function testCreateContainerNodeInPersonalFolder($containerName = 'testcontainer')
    {
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName
            . '/' . $containerName;
        $result = $this->_getUit()->createNodes($testPath, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
        $createdNode = $result[0];
        
        $this->assertTrue(isset($createdNode['name']));
        $this->assertEquals($containerName, $createdNode['name']);
        $this->assertEquals(Tinebase_Core::getUser()->getId(), $createdNode['created_by']['accountId']);
        
        return $createdNode;
    }

    /**
     * create container with bad name
     * 
     * @see 0006524: Access Problems via Webdav
     */
    public function testCreateContainerNodeWithBadName()
    {
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcon/tainer';
        
        $this->setExpectedException('Tinebase_Exception_NotFound');
        $result = $this->_getUit()->createNodes($testPath, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
    }

    /**
     * create container in shared folder
     *
     * @param string $_name
     * @return array created node
     */
    public function testCreateContainerNodeInSharedFolder($_name = 'testcontainer')
    {
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/' . $_name;
        $result = $this->_getUit()->createNode($testPath, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, null, false);
        $createdNode = $result;
        
        $this->assertEquals($_name, $createdNode['name']);
        $this->assertEquals($testPath, $createdNode['path']);
        
        return $createdNode;
    }

    /**
     * testCreateFileNodes
     *
     * @param bool $_addData
     * @return array file paths
     */
    public function testCreateFileNodes($_addData = false)
    {
        $sharedContainerNode = $this->testCreateContainerNodeInSharedFolder();
        
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($sharedContainerNode['path']);
        
        $filepaths = array(
            $sharedContainerNode['path'] . '/file1',
            $sharedContainerNode['path'] . '/file2',
        );

        $tempFileIds = array();
        if (true === $_addData) {
            for ($i = 0; $i < 2; ++$i) {
                $tempPath = Tinebase_TempFile::getTempPath();
                $tempFileIds[] = Tinebase_TempFile::getInstance()->createTempFile($tempPath);
                file_put_contents($tempPath, 'someData');
            }
        }

        $result = $this->_getUit()->createNodes($filepaths, Tinebase_Model_Tree_FileObject::TYPE_FILE, $tempFileIds, false);
        
        $this->assertEquals(2, count($result));
        $this->assertEquals('file1', $result[0]['name']);
        $this->assertEquals(Tinebase_Model_Tree_FileObject::TYPE_FILE, $result[0]['type']);
        $this->assertEquals('file2', $result[1]['name']);
        $this->assertEquals(Tinebase_Model_Tree_FileObject::TYPE_FILE, $result[1]['type']);
        
        return $filepaths;
    }


    /**
     * testCreateFileNodeInPersonalRoot
     */
    public function testCreateFileNodeInPersonalRoot()
    {
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL
            . '/' . Tinebase_Core::getUser()->accountLoginName
            . '/' . 'file1';


        $tempPath = Tinebase_TempFile::getTempPath();
        $tempFileIds = array(Tinebase_TempFile::getInstance()->createTempFile($tempPath));
        file_put_contents($tempPath, 'someData');

        try {
            $result = $this->_getUit()->createNodes(array($testPath), Tinebase_Model_Tree_FileObject::TYPE_FILE,
                $tempFileIds, false);
            self::fail('it is not allowed to create new files here');
        } catch (Tinebase_Exception_AccessDenied $tead) {
            self::assertContains('No permission to add nodes in path', $tead->getMessage());
        }
    }

    /**
     * testMoveFileNode
     */
    public function testMoveFileNode()
    {
        $filePaths = $this->testCreateFileNodes(true);
        $secondFolderNode = $this->testCreateContainerNodeInSharedFolder('fooContainer');

        $file0Path = Filemanager_Controller_Node::getInstance()->addBasePath($filePaths[0]);
        $targetPath = Filemanager_Controller_Node::getInstance()->addBasePath($secondFolderNode['path']);

        $parentFolder = $this->_fsController->stat(dirname($file0Path));
        static::assertEquals(16, $parentFolder->size, 'two files with 8 bytes each created, excpected 16 bytes folder size');
        static::assertEquals(0, $secondFolderNode['size'], 'expect new folder to be empty');

        $this->_getUit()->moveNodes($file0Path, $targetPath, false);

        $parentFolder = $this->_fsController->stat(dirname($file0Path));
        static::assertEquals(8, $parentFolder->size, 'one file with 8 bytes expected');
        $secondFolderNode = $this->_fsController->stat($targetPath);
        static::assertEquals(8, $secondFolderNode->size, 'one file with 8 bytes expected');

    }

    /**
    * testCreateFileNodeWithUTF8Filenames
    * 
    * @see 0006068: No umlauts at beginning of file names / https://forge.tine20.org/mantisbt/view.php?id=6068
    * @see 0006150: Problem loading files with russian file names / https://forge.tine20.org/mantisbt/view.php?id=6150
    * 
    * @return array first created node
    */
    public function testCreateFileNodeWithUTF8Filenames()
    {
        $personalContainerNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $testPaths = array($personalContainerNode['path'] . '/ütest.eml', $personalContainerNode['path'] . '/Безимени.txt');
        $result = $this->_getUit()->createNodes($testPaths, Tinebase_Model_Tree_FileObject::TYPE_FILE, array(), false);
    
        $this->assertEquals(2, count($result));
        $this->assertEquals('ütest.eml', $result[0]['name']);
        $this->assertEquals('Безимени.txt', $result[1]['name']);
        $this->assertEquals(Tinebase_Model_Tree_FileObject::TYPE_FILE, $result[0]['type']);
        
        return $result[0];
    }
    
    /**
     * testCreateFileNodeWithTempfile
     * 
     * @return array node
     */
    public function testCreateFileNodeWithTempfile()
    {
        $sharedContainerNode = $this->testCreateContainerNodeInSharedFolder();
        
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($sharedContainerNode['path']);
        
        $filepath = $sharedContainerNode['path'] . '/test.txt';
        // create empty file first (like the js frontend does)
        $result = $this->_getUit()->createNode($filepath, Tinebase_Model_Tree_FileObject::TYPE_FILE, array(), false);

        $tempFileBackend = new Tinebase_TempFile();
        $tempFile = $tempFileBackend->createTempFile(dirname(dirname(__FILE__)) . '/files/test.txt');
        $result = $this->_getUit()->createNode($filepath, Tinebase_Model_Tree_FileObject::TYPE_FILE, $tempFile->getId(), true);
        
        $this->assertEquals('text/plain', $result['contenttype'], print_r($result, true));
        $this->assertEquals(17, $result['size']);
        
        return $result;
    }
    
    /**
     * testCreateFileCountTempDir
     * 
     * @see 0007370: Unable to upload files
     */
    public function testCreateFileCountTempDir()
    {
        $tmp = Tinebase_Core::getTempDir();
        $filecountInTmpBefore = count(scandir($tmp));
        
        $this->testCreateFileNodeWithTempfile();
        
        // check if tempfile has been created in tine20 tempdir
        $filecountInTmpAfter = count(scandir($tmp));
        
        $this->assertEquals($filecountInTmpBefore + 1, $filecountInTmpAfter, '1 tempfiles should have been created');
    }

    /**
     * testCreateDirectoryNodesInShared
     * 
     * @return array dir paths
     */
    public function testCreateDirectoryNodesInShared()
    {
        $sharedContainerNode = $this->testCreateContainerNodeInSharedFolder();
        
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($sharedContainerNode['path']);
        
        $dirpaths = array(
            $sharedContainerNode['path'] . '/dir1',
            $sharedContainerNode['path'] . '/dir2',
        );
        $result = $this->_getUit()->createNodes($dirpaths, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
        
        $this->assertEquals(2, count($result));
        $this->assertEquals('dir1', $result[0]['name']);
        $this->assertEquals('dir2', $result[1]['name']);
        
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => $sharedContainerNode['path']
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FOLDER,
        ));
        $result = $this->_getUit()->searchNodes($filter, array('sort' => 'creation_time'));
        $this->assertEquals(2, $result['totalcount']);
        
        return $dirpaths;
    }

    /**
     * testCreateDirectoryNodesInPersonal
     * 
     * @return array dir paths
     */
    public function testCreateDirectoryNodesInPersonal()
    {
        $personalContainerNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($personalContainerNode['path']);
        
        $dirpaths = array(
            $personalContainerNode['path'] . '/dir1',
            $personalContainerNode['path'] . '/dir2',
        );
        $result = $this->_getUit()->createNodes($dirpaths, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
        
        $this->assertEquals(2, count($result));
        $this->assertEquals('dir1', $result[0]['name']);
        $this->assertEquals('dir2', $result[1]['name']);
        
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => $personalContainerNode['path']
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FOLDER,
        ));
        $result = $this->_getUit()->searchNodes($filter, array('sort' => 'contenttype'));
        $this->assertEquals(2, $result['totalcount']);
        
        return $dirpaths;
    }
    
    /**
     * testCreateDirectoryNodeInPersonalWithSameNameAsOtherUsersDir
     * 
     * @see 0008044: could not create a personal folder with the name of a folder of another user
     */
    public function testCreateDirectoryNodeInPersonalWithSameNameAsOtherUsersDir()
    {
        $this->testCreateContainerNodeInPersonalFolder();
        
        $personas = Zend_Registry::get('personas');
        Tinebase_Core::set(Tinebase_Core::USER, $personas['sclever']);
        $personalContainerNodeOfsclever = $this->testCreateContainerNodeInPersonalFolder();
        
        $this->assertEquals('/personal/sclever/testcontainer', $personalContainerNodeOfsclever['path']);
    }

    /**
     * testUpdateNodeWithCustomfield
     *
     * ·@see 0009292: Filemanager Custom Fields not saved
     */
    public function testUpdateNodeWithCustomfield()
    {
        $cf = $this->_createCustomfield('fmancf', 'Filemanager_Model_Node');
        $personalContainerNode = $this->testCreateContainerNodeInPersonalFolder();

        $personalContainerNode = $this->_getUit()->getNode($personalContainerNode['id']);
        $personalContainerNode['customfields'][$cf->name] = 'cf value';
        $personalContainerNode['revisionProps']['nodeId'] = $personalContainerNode['id'];
        $personalContainerNode['revisionProps']['keep'] = true;
        $personalContainerNode['revisionProps']['keepNum'] = 3;
        $personalContainerNode['revisionProps']['keepMonth'] = 4;
        $updatedNode = $this->_getUit()->saveNode($personalContainerNode);

        static::assertTrue(isset($updatedNode['customfields']) && isset($updatedNode['customfields'][$cf->name]),
            'no customfields in record');
        static::assertEquals($personalContainerNode['customfields'][$cf->name],
            $updatedNode['customfields'][$cf->name]);

        static::assertTrue(isset($updatedNode['revisionProps']) && isset($updatedNode['revisionProps']['keep']) &&
            isset($updatedNode['revisionProps']['keepNum']) && isset($updatedNode['revisionProps']['keepMonth']),
            'revisionProps not saved: ' . print_r($updatedNode, true));
        static::assertEquals($personalContainerNode['revisionProps']['nodeId'], $personalContainerNode['id']);
        static::assertEquals($personalContainerNode['revisionProps']['keep'], true);
        static::assertEquals($personalContainerNode['revisionProps']['keepNum'], 3);
        static::assertEquals($personalContainerNode['revisionProps']['keepMonth'], 4);
    }
    
    /**
     * testRenameDirectoryNodeInPersonalToSameNameAsOtherUsersDir
     *
     * @see 0008046: Rename personal folder to personal folder of another user
     */
    public function testRenameDirectoryNodeInPersonalToSameNameAsOtherUsersDir()
    {
        $personalContainerNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $personas = Zend_Registry::get('personas');
        Tinebase_Core::set(Tinebase_Core::USER, $personas['sclever']);
        $personalContainerNodeOfsclever = $this->testCreateContainerNodeInPersonalFolder('testcontainer2');
        
        $this->assertEquals('/personal/sclever/testcontainer2', $personalContainerNodeOfsclever['path']);
        
        // rename
        $newPath = '/personal/sclever/testcontainer';
        $result = $this->_getUit()->moveNodes(array($personalContainerNodeOfsclever['path']), array($newPath), false);
        $this->assertEquals(1, count($result));
        $this->assertEquals($newPath, $result[0]['path']);
    }
    
    /**
     * testCopyFolderNodes
     */
    public function testCopyFolderNodesToFolder()
    {
        $dirsToCopy = $this->testCreateDirectoryNodesInShared();
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $result = $this->_getUit()->copyNodes($dirsToCopy, $targetNode['path'], false);
        $this->assertEquals(2, count($result));
        $this->assertEquals($targetNode['path'] . '/dir1', $result[0]['path']);
    }

    /**
     * testCopyContainerNode
     */
    public function testCopyContainerNode()
    {
        $sharedContainerNode = $this->testCreateContainerNodeInSharedFolder();
        $target = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName;
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($target . '/testcontainer');
        $result = $this->_getUit()->copyNodes($sharedContainerNode['path'], $target, false);
        $this->assertEquals(1, count($result));
        $this->assertTrue(isset($result[0]['name']), print_r($result, true));
        $this->assertEquals('testcontainer', $result[0]['name']);
    }
    
    /**
     * testCopyFileNodesToFolder
     * 
     * @return array target node
     */
    public function testCopyFileNodesToFolder()
    {
        $filesToCopy = $this->testCreateFileNodes();
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $result = $this->_getUit()->copyNodes($filesToCopy, $targetNode['path'], false);
        $this->assertEquals(2, count($result));
        $this->assertEquals($targetNode['path'] . '/file1', $result[0]['path']);
        
        return $targetNode;
    }

    /**
     * testCopyFolderWithNodes
     */
    public function testCopyFolderWithNodes()
    {
        $filesToCopy = $this->testCreateFileNodes();
        $target = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName;
        
        $result = $this->_getUit()->copyNodes(
            '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/testcontainer',
            $target, 
            false
        );
        
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($target . '/testcontainer');
        $this->assertEquals(1, count($result));

        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer',
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FILE,
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->assertEquals(2, $result['totalcount']);
    }
    
    /**
     * testCopyFileWithContentToFolder
     */
    public function testCopyFileWithContentToFolder()
    {
        $fileToCopy = $this->testCreateFileNodeWithTempfile();
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $result = $this->_getUit()->copyNodes($fileToCopy['path'], $targetNode['path'], false);
        $this->assertEquals(1, count($result));
        $this->assertEquals($targetNode['path'] . '/test.txt', $result[0]['path']);
        $this->assertEquals('text/plain', $result[0]['contenttype']);
    }
    
    /**
     * testCopyFileNodeToFileExisting
     */
    public function testCopyFileNodeToFileExisting()
    {
        $filesToCopy = $this->testCreateFileNodes();
        $file1 = $filesToCopy[0];
        $file2 = $filesToCopy[1];
        
        $this->setExpectedException('Filemanager_Exception_NodeExists');
        $result = $this->_getUit()->copyNodes(array($file1), array($file2), false);
    }
    
    /**
     * testCopyFileNodeToFileExistingCatchException
     */
    public function testCopyFileNodeToFileExistingCatchException()
    {
        $filesToCopy = $this->testCreateFileNodes();
        $file1 = $filesToCopy[0];
        $file2 = $filesToCopy[1];
        
        try {
            $result = $this->_getUit()->copyNodes(array($file1), array($file2), false);
        } catch (Filemanager_Exception_NodeExists $fene) {
            $info = $fene->toArray();
            $this->assertEquals(1, count($info['existingnodesinfo']));
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    /**
     * testMoveFolderNodesToFolder
     */
    public function testMoveFolderNodesToFolder()
    {
        $dirsToMove = $this->testCreateDirectoryNodesInShared();
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $result = $this->_getUit()->moveNodes($dirsToMove, $targetNode['path'], false);
        $this->assertEquals(2, count($result));
        $this->assertEquals($targetNode['path'] . '/dir1', $result[0]['path'], 'no new path: ' . print_r($result, true));
        
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/testcontainer'
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FOLDER,
        ));

        $result = $this->_getUit()->searchNodes($filter, array());
        $this->assertEquals(0, $result['totalcount']);
    }
    
    /**
     * testMoveFolderNodesToFolderExisting
     * 
     * @see 0007028: moving a folder to another folder with a folder with the same name
     */
    public function testMoveFolderNodesToFolderExisting()
    {
        sleep(1);
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/dir1';
        $result = $this->_getUit()->moveNodes(array($targetNode['path']), array($testPath), false);
        $dirs = $this->testCreateDirectoryNodesInShared();
        try {
            $result = $this->_getUit()->moveNodes(array($testPath), '/shared/testcontainer', false);
            $this->fail('Expected Filemanager_Exception_NodeExists!');
        } catch (Filemanager_Exception_NodeExists $fene) {
            $result = $this->_getUit()->moveNodes(array($testPath), '/shared/testcontainer', true);
            $this->assertEquals(1, count($result));
            $this->assertEquals('/shared/testcontainer/dir1', $result[0]['path']);
        }
    }

    /**
     * testMoveContainerFolderNodeToExistingContainer
     * 
     * @see 0007028: moving a folder to another folder with a folder with the same name
     */
    public function testMoveContainerFolderNodeToExistingContainer()
    {
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $testPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer2';
        $result = $this->_getUit()->createNodes($testPath, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
        $createdNode = $result[0];

        try {
            $result = $this->_getUit()->moveNodes(array($targetNode['path']), array($createdNode['path']), false);
            $this->fail('Expected Filemanager_Exception_NodeExists!');
        } catch (Filemanager_Exception_NodeExists $fene) {
            $result = $this->_getUit()->moveNodes(array($targetNode['path']), array($createdNode['path']), true);
            $this->assertEquals(1, count($result));
            $this->assertEquals($testPath, $result[0]['path']);
        }
    }
    
    /**
    * testMoveFolderNodesToTopLevel
    */
    public function testMoveFolderNodesToTopLevel()
    {
        // we need the personal folder for the test user
        $this->_setupTestPath(Tinebase_FileSystem::FOLDER_TYPE_PERSONAL);
        
        $dirsToMove = $this->testCreateDirectoryNodesInShared();
        $targetPath = '/personal/' . Tinebase_Core::getUser()->accountLoginName;
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($targetPath . '/dir1');
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($targetPath . '/dir2');
        
        $result = $this->_getUit()->moveNodes($dirsToMove, $targetPath, false);
        $this->assertEquals(2, count($result));
        $this->assertEquals($targetPath . '/dir1', $result[0]['path']);
    }
    
    /**
     * testMoveContainerFolderNodesToContainerFolder
     */
    public function testMoveContainerFolderNodesToContainerFolder()
    {
        $sourceNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $newPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainermoved';
        $result = $this->_getUit()->moveNodes($sourceNode['path'], array($newPath), false);
        $this->assertEquals(1, count($result));
        $this->assertEquals($newPath, $result[0]['path'], 'no new path: ' . print_r($result, true));

        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FOLDER,
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        foreach ($result['results'] as $node) {
            $this->assertNotEquals($sourceNode['path'], $node['path']);
        }
    }
    
    /**
     * testMoveContainerFolderNodesToContainerFolderWithChildNodes
     */
    public function testMoveContainerFolderNodesToContainerFolderWithChildNodes()
    {
        $children = $this->testCreateDirectoryNodesInPersonal();
        
        $oldPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer';
        $newPath = $oldPath . 'moved';
        $result = $this->_getUit()->moveNodes(array($oldPath), array($newPath), false);
        $this->assertEquals(1, count($result));
        $this->assertEquals($newPath, $result[0]['path']);

        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => $newPath
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FOLDER,
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->assertEquals(2, $result['totalcount']);
    }
    
    /**
     * testMoveFileNodesToFolder
     * 
     * @return array target node
     */
    public function testMoveFileNodesToFolder()
    {
        $filesToMove = $this->testCreateFileNodes();
        $targetNode = $this->testCreateContainerNodeInPersonalFolder();
        
        $result = $this->_getUit()->moveNodes($filesToMove, $targetNode['path'], false);
        $this->assertEquals(2, count($result));
        $this->assertEquals($targetNode['path'] . '/file1', $result[0]['path']);

        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/testcontainer'
        ), array(
            'field'    => 'type', 
            'operator' => 'equals', 
            'value'    => Tinebase_Model_Tree_FileObject::TYPE_FILE,
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        $this->assertEquals(0, $result['totalcount']);
        
        return $targetNode;
    }

    /**
     * testMoveFileNodesOverwrite
     */
    public function testMoveFileNodesOverwrite()
    {
        $targetNode = $this->testCopyFileNodesToFolder();
        
        $sharedContainerPath = '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/testcontainer/';
        $filesToMove = array($sharedContainerPath . 'file1', $sharedContainerPath . 'file2');
        $result = $this->_getUit()->moveNodes($filesToMove, $targetNode['path'], true);
        
        $this->assertEquals(2, count($result));
    }
    
    /**
     * testMoveFolderNodeToRoot
     */
    public function testMoveFolderNodeToRoot()
    {
        $children = $this->testCreateDirectoryNodesInPersonal();
        
        $target = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName;
        $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($target . '/testcontainer');
        $result = $this->_getUit()->moveNodes($children[0], $target, false);
        $this->assertEquals(1, count($result));
        $this->assertEquals('dir1', $result[0]['name'], print_r($result[0], true));
    }

    /**
     * Test if notes are correctly decorated with path field
     */
    public function testGetNode()
    {
        $node = $this->testCreateContainerNodeInPersonalFolder();

        $result = Filemanager_Controller_Node::getInstance()->get($node['id']);

        $this->assertTrue($result->path != "");
        $this->assertEquals('/personal/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer', $result->path);
    }

    /**
     * testDeleteContainerNode
     */
    public function testDeleteContainerNode()
    {
        $sharedContainerNode = $this->testCreateContainerNodeInSharedFolder();
        
        $this->_getUit()->deleteNodes($sharedContainerNode['path']);

        // check if node is deleted
        $this->setExpectedException('Tinebase_Exception_NotFound');
        $this->_fsController->stat($sharedContainerNode['path']);
    }

    /**
     * testDeleteFileNodes
     */
    public function testDeleteFileNodes($_addData = false)
    {
        $filepaths = $this->testCreateFileNodes($_addData);

        if (true === $_addData) {
            $parentFolder = $this->_fsController->stat(Filemanager_Controller_Node::getInstance()->addBasePath(dirname($filepaths[0])));
            static::assertEquals(16, $parentFolder->size, 'two files with 8 bytes each created, excpected 16 bytes folder size');
        }
        
        $this->_getUit()->deleteNodes($filepaths);

        if (true === $_addData) {
            $parentFolder = $this->_fsController->stat(Filemanager_Controller_Node::getInstance()->addBasePath(dirname($filepaths[0])));
            static::assertEquals(0, $parentFolder->size, 'after deletion expected 0 byte folder size');
        }

        // check if node is deleted
        try {
            $this->_fsController->stat(Filemanager_Controller_Node::getInstance()->addBasePath($filepaths[0]));
            $this->assertTrue(false);
        } catch (Tinebase_Exception_NotFound $tenf) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * test cleanup of deleted files (database)
     * 
     * @see 0008062: add cleanup script for deleted files
     */
    public function testDeletedFileCleanupFromDatabase()
    {
        $fileNode = $this->testCreateFileNodeWithTempfile();
        
        // get "real" filesystem path + unlink
        $fileObjectBackend = new Tinebase_Tree_FileObject();
        $fileObject = $fileObjectBackend->get($fileNode['object_id']);
        unlink($fileObject->getFilesystemPath());
        
        $result = Tinebase_FileSystem::getInstance()->clearDeletedFilesFromDatabase();
        $this->assertEquals(1, $result, 'should cleanup one file');

        $result = Tinebase_FileSystem::getInstance()->clearDeletedFilesFromDatabase();
        $this->assertEquals(0, $result, 'should cleanup no file');
        
        // node should no longer be found
        try {
            $this->_getUit()->getNode($fileNode['id']);
            $this->fail('tree node still exists: ' . print_r($fileNode, true));
        } catch (Tinebase_Exception_NotFound $tenf) {
            $this->assertEquals('Tinebase_Model_Tree_Node record with id = ' . $fileNode['id'] . ' not found!', $tenf->getMessage());
        }
    }
    
    /**
     * testDeleteDirectoryNodes
     */
    public function testDeleteDirectoryNodes()
    {
        $dirpaths = $this->testCreateDirectoryNodesInShared();
        
        $result = $this->_getUit()->deleteNodes($dirpaths);

        // check if node is deleted
        $this->setExpectedException('Tinebase_Exception_NotFound');
        $node = $this->_fsController->stat(Filemanager_Controller_Node::getInstance()->addBasePath($dirpaths[0]));
    }
    
    /**
     * testGetUpdate
     * 
     * @see 0006736: Create File (Edit)InfoDialog
     * @return array
     */
    public function testGetUpdate()
    {
        $this->testCreateFileNodes();
        $filter = array(array(
            'field'    => 'path', 
            'operator' => 'equals', 
            'value'    => '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/testcontainer'
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        
        $this->assertEquals(2, $result['totalcount']);
        $initialNode = $result['results'][0];
        
        $node = $this->_getUit()->getNode($initialNode['id']);
        $this->assertEquals('file', $node['type']);
        
        $node['description'] = 'UNITTEST';
        $node = $this->_getUit()->saveNode($node);
        
        $this->assertEquals('UNITTEST', $node['description']);
        $this->assertEquals($initialNode['contenttype'], $node['contenttype'], 'contenttype  not preserved');

        
        return $node;
    }

    /**
     * testAttachTag
     *
     * @see 0012284: file type changes to 'directory' if tag is assigned
     */
    public function testAttachTagPreserveContentType()
    {
        $node = $this->testCreateFileNodeWithTempfile();
        $node['tags'] = array(array(
            'type'          => Tinebase_Model_Tag::TYPE_PERSONAL,
            'name'          => 'file tag',
        ));
        $node['path'] = '';
        // remove hash field that the client does not send
        unset($node['hash']);
        $updatedNode = $this->_getUit()->saveNode($node);

        $this->assertEquals(1, count($updatedNode['tags']));
        $this->assertEquals($node['contenttype'], $updatedNode['contenttype'], 'contenttype  not preserved');
    }

    /**
     * testSetRelation
     * 
     * @see 0006736: Create File (Edit)InfoDialog
     */
    public function testSetRelation()
    {
        $node = $this->testGetUpdate();
        $node['relations'] = array($this->_getRelationData($node));
        $node = $this->_getUit()->saveNode($node);
        
        $this->assertEquals(1, count($node['relations']));
        $this->assertEquals('PHPUNIT, ali', $node['relations'][0]['related_record']['n_fileas']);
        
        $adbJson = new Addressbook_Frontend_Json();
        $contact = $adbJson->getContact($node['relations'][0]['related_id']);
        $this->assertEquals(1, count($contact['relations']), 'relations are missing');
        $this->assertEquals($node['name'], $contact['relations'][0]['related_record']['name']);
    }
    
    /**
     * get contact relation data
     * 
     * @param array $node
     * @return array
     */
    protected function _getRelationData($node)
    {
        return array(
            'own_model'              => 'Filemanager_Model_Node',
            'own_backend'            => 'Sql',
            'own_id'                 => $node['id'],
            'related_degree'         => Tinebase_Model_Relation::DEGREE_SIBLING,
            'type'                   => 'FILE',
            'related_backend'        => 'Sql',
            'related_model'          => 'Addressbook_Model_Contact',
            'remark'                 => null,
            'related_record'         => array(
                'n_given'           => 'ali',
                'n_family'          => 'PHPUNIT',
                'org_name'          => Tinebase_Record_Abstract::generateUID(),
                'tel_cell_private'  => '+49TELCELLPRIVATE',
            )
        );
    }

    /**
     * testSetRelationToFileInPersonalFolder
     * 
     * @see 0006736: Create File (Edit)InfoDialog
     */
    public function testSetRelationToFileInPersonalFolder()
    {
        $node = $this->testCreateFileNodeWithUTF8Filenames();
        $node['relations'] = array($this->_getRelationData($node));
        $node = $this->_getUit()->saveNode($node);
        
        $adbJson = new Addressbook_Frontend_Json();
        $contact = $adbJson->getContact($node['relations'][0]['related_id']);
        $this->assertEquals(1, count($contact['relations']));
        $relatedNode = $contact['relations'][0]['related_record'];
        $this->assertEquals($node['name'], $relatedNode['name']);
        $pathRegEx = '@^/personal/[a-f0-9-]+/testcontainer/' . preg_quote($relatedNode['name']) . '$@';
        $this->assertTrue(preg_match($pathRegEx, $relatedNode['path']) === 1, 'path mismatch: ' . print_r($relatedNode, true) . ' regex: ' . $pathRegEx);
    }
    
    /**
     * test renaming a folder in a folder containing a folder with the same name
     *
     * @see: https://forge.tine20.org/mantisbt/view.php?id=10132
     */
     public function testRenameFolderInFolderContainingFolderAlready()
     {
        $path = '/personal/' .Tinebase_Core::getUser()->accountLoginName . '/' . $this->_getPersonalFilemanagerContainer()->name;
     
        $this->_getUit()->createNode($path . '/Test1', 'folder', null, false);
        $this->_getUit()->createNode($path . '/Test1/Test2', 'folder', null, false);
        $this->_getUit()->createNode($path . '/Test1/Test3', 'folder', null, false);
        
        $this->setExpectedException('Filemanager_Exception_NodeExists');
        
        $this->_getUit()->moveNodes(array($path . '/Test1/Test3'), array($path . '/Test1/Test2'), false);
     }
    
    /**
     * tests the recursive filter
     */
    public function testSearchRecursiveFilter()
    {
        $fixtures = array(
            array('/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testa', 'color-red.gif'),
            array('/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testb', 'color-green.gif'),
            array('/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testc', 'color-blue.gif'));

        $tempFileBackend = new Tinebase_TempFile();
        
        foreach($fixtures as $path) {
            $node = $this->_getUit()->createNode($path[0], Tinebase_Model_Tree_FileObject::TYPE_FOLDER, null, false);
            
            $this->assertEquals(str_replace('/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/', '', $path[0]), $node['name']);
            $this->assertEquals($path[0], $node['path']);
            
            $this->_objects['paths'][] = Filemanager_Controller_Node::getInstance()->addBasePath($node['path']);
    
            $filepath = $node['path'] . '/' . $path[1];
            // create empty file first (like the js frontend does)
            $result = $this->_getUit()->createNode($filepath, Tinebase_Model_Tree_FileObject::TYPE_FILE, array(), false);
            $tempFile = $tempFileBackend->createTempFile(dirname(dirname(__FILE__)) . '/files/' . $path[1]);
            $result = $this->_getUit()->createNode($filepath, Tinebase_Model_Tree_FileObject::TYPE_FILE, $tempFile->getId(), true);
        }
        
        $filter = array(
            array('field' => 'recursive', 'operator' => 'equals',   'value' => 1),
            array('field' => 'path',      'operator' => 'equals',   'value' => '/'),
            array('field' => 'query',     'operator' => 'contains', 'value' => 'color'),
            array('field' => 'isIndexed', 'operator' => 'equals',   'value' => 0),
        'AND');
        
        $result = $this->_getUit()->searchNodes($filter, array('sort' => 'name', 'start' => 0, 'limit' => 0));
        $this->assertEquals(3, count($result), '3 files should have been found!');
    }
    
    /**
     * test cleanup of deleted files (filesystem)
     */
    public function testDeletedFileCleanupFromFilesystem()
    {
        // remove all files with size 0 first
        $size0Nodes = Tinebase_FileSystem::getInstance()->searchNodes(new Tinebase_Model_Tree_Node_Filter(array(
            array('field' => 'type', 'operator' => 'equals', 'value' => Tinebase_Model_Tree_FileObject::TYPE_FILE),
            array('field' => 'size', 'operator' => 'equals', 'value' => 0)
        )));
        foreach ($size0Nodes as $node) {
            Tinebase_FileSystem::getInstance()->deleteFileNode($node);
        }
        
        $this->testDeleteFileNodes();
        $result = Tinebase_FileSystem::getInstance()->clearDeletedFilesFromFilesystem();
        $this->assertEquals(0, $result, 'should not clean up anything as files with size 0 are not written to disk');
        $this->tearDown();
        
        Tinebase_TransactionManager::getInstance()->startTransaction(Tinebase_Core::getDb());
        $this->testDeleteFileNodes(true);
        $result = Tinebase_FileSystem::getInstance()->clearDeletedFilesFromFilesystem();
        $this->assertEquals(1, $result, 'should cleanup one file');
    }
    
    /**
     * test preventing to copy a folder in its subfolder
     * 
     * @see: https://forge.tine20.org/mantisbt/view.php?id=9990
     */
    public function testMoveFolderIntoChildFolder()
    {
        $this->_getUit()->createNode('/shared/Parent', 'folder', null, false);
        $this->_getUit()->createNode('/shared/Parent/Child', 'folder', null, false);
        
        $this->setExpectedException('Filemanager_Exception_DestinationIsOwnChild');
        
        // this must not work
        $this->_getUit()->moveNodes(array('/shared/Parent'), array('/shared/Parent/Child/Parent'), false);
    }
    
    /**
     * test exception on moving to the same position
     * 
     * @see: https://forge.tine20.org/mantisbt/view.php?id=9990
     */
    public function testMoveFolderToSamePosition()
    {
        $this->_getUit()->createNode('/shared/Parent', 'folder', null, false);
        $this->_getUit()->createNode('/shared/Parent/Child', 'folder', null, false);
    
        $this->setExpectedException('Filemanager_Exception_DestinationIsSameNode');
    
        // this must not work
        $this->_getUit()->moveNodes(array('/shared/Parent/Child'), array('/shared/Parent/Child'), false);
    }

    /**
     * test to move a folder containing another folder
     *
     * @see: https://forge.tine20.org/mantisbt/view.php?id=9990
     */
    public function testMove2FoldersOnToplevel()
    {
        $path = '/personal/' .Tinebase_Core::getUser()->accountLoginName . '/' . $this->_getPersonalFilemanagerContainer()->name;
    
        $this->_getUit()->createNode($path . '/Parent', 'folder', null, false);
        $this->_getUit()->createNode($path . '/Parent/Child', 'folder', null, false);
        $this->_getUit()->createNode('/shared/Another', 'folder', null, false);
    
        // move forth and back, no exception should occur
        $this->_getUit()->moveNodes(array($path . '/Parent'), array('/shared/Parent'), false);
        $this->_getUit()->moveNodes(array('/shared/Parent'), array($path . '/Parent'), false);
    
        try {
            $c = Tinebase_Container::getInstance()->getContainerByName('Filemanager', 'Parent', Tinebase_FileSystem::FOLDER_TYPE_SHARED);
            $this->fail('Container doesn\'t get deleted');
        } catch (Tinebase_Exception_NotFound $e) {
        }
        
        // may be any exception
        $e = new Tinebase_Exception('Dog eats cat');
    
        try {
            $this->_getUit()->moveNodes(array($path . '/Parent'), array('/shared/Parent'), false);
        } catch (Filemanager_Exception_NodeExists $e) {
        }
    
        // if $e gets overridden, an error occured (the exception Filemanager_Exception_NodeExists must not be thrown)
        $this->assertEquals('Tinebase_Exception', get_class($e));
    }
    
    /**
     * test creating a folder in a folder with the same name (below personal folders)
     *
     * @see: https://forge.tine20.org/mantisbt/view.php?id=10132
     */
    public function testCreateFolderInFolderWithSameName()
    {
        $path = '/personal/' .Tinebase_Core::getUser()->accountLoginName . '/' . $this->_getPersonalFilemanagerContainer()->name;

        $result = $this->_getUit()->createNode($path . '/Test1', 'folder', null, false);
        $this->assertTrue(isset($result['id']));
        $result = $this->_getUit()->createNode($path . '/Test1/Test1', 'folder', null, false);
        $this->assertTrue(isset($result['id']), 'node has not been created');
        $e = new Tinebase_Exception('nothing');
        try {
            $this->_getUit()->createNode($path . '/Test1/Test1/Test2', 'folder', null, false);
        } catch (Exception $e) {
            $this->fail('The folder couldn\'t be found, so it hasn\'t ben created');
        }
        
        $this->assertEquals('nothing', $e->getMessage());
    }
    
    /**
     * get other users container
     * 
     * @return Tinebase_Model_Container
     */
    protected function _getOtherUserContainer()
    {
        if (!$this->_otherUserContainer) {
            $sclever = $this->_personas['sclever'];

            $path = Tinebase_FileSystem::getInstance()->getApplicationBasePath(
                $this->_application, Tinebase_FileSystem::FOLDER_TYPE_PERSONAL) . '/' . $sclever->getId() . '/clever';
            if (Tinebase_FileSystem::getInstance()->fileExists($path)) {
                $this->_otherUserContainer = Tinebase_FileSystem::getInstance()->stat($path);
            } else {
                $grants = Tinebase_Model_Grants::getPersonalGrants($sclever);
                $grants->addRecord(new Tinebase_Model_Grants(array(
                    'account_type' => Tinebase_Acl_Rights::ACCOUNT_TYPE_ANYONE,
                    'account_id' => 0,
                    Tinebase_Model_Grants::GRANT_READ => true,
                )));
                $this->_otherUserContainer = Tinebase_FileSystem::getInstance()->createAclNode($path, $grants);
            }
        }
        
        return $this->_otherUserContainer;
    }
    
    /**
     * get personal container
     * 
     * @return Tinebase_Model_Tree_Node
     */
    protected function _getPersonalFilemanagerContainer()
    {
        if (!$this->_personalContainer) {
            $user = Tinebase_Core::getUser();
            $this->_personalContainer = Tinebase_FileSystem::getInstance()->getPersonalContainer($user, 'Filemanager', $user)->getFirstRecord();
        }
        
        return $this->_personalContainer;
    }
    
    /**
     * get shared container
     * 
     * @return Tinebase_Model_Container
     */
    protected function _getSharedContainer()
    {
        if (!$this->_sharedContainer) {
            $path = Tinebase_FileSystem::getInstance()->getApplicationBasePath(
                    $this->_application, Tinebase_FileSystem::FOLDER_TYPE_SHARED) . '/shared';
            try {
                $this->_sharedContainer = Tinebase_FileSystem::getInstance()->stat($path);
            } catch (Tinebase_Exception_NotFound $tenf) {
                $this->_sharedContainer = Tinebase_FileSystem::getInstance()->createAclNode($path);
            }
        }
        
        return $this->_sharedContainer;
    }
    
    /**
     * setup the test paths
     * 
     * @param string|array $_types
     */
    protected function _setupTestPath($_types)
    {
        $testPaths = array();
        $types = (array) $_types;
        
        foreach ($types as $type) {
            switch ($type) {
                case Tinebase_FileSystem::FOLDER_TYPE_PERSONAL:
                    $testPaths[] = Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->getId() . '/' 
                        . $this->_getPersonalFilemanagerContainer()->name . '/unittestdir_personal';
                    break;
                case Tinebase_FileSystem::FOLDER_TYPE_SHARED:
                    $testPaths[] = Tinebase_FileSystem::FOLDER_TYPE_SHARED . '/' . $this->_getSharedContainer()->name . '/unittestdir_shared';
                    break;
                case Tinebase_Model_Container::TYPE_OTHERUSERS:
                    $personas = Zend_Registry::get('personas');
                    $testPaths[] = Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . $personas['sclever']->getId() . '/' 
                        . $this->_getOtherUserContainer()->name . '/unittestdir_other';
                    break;
            }
        }
        
        foreach ($testPaths as $path) {
            $path = Filemanager_Controller_Node::getInstance()->addBasePath($path);
            $this->_objects['paths'][] = $path;
            $this->_fsController->mkdir($path);
        }
    }
    
    /**
     * testSaveDownloadLinkFile
     * 
     * @return array Filemanager_Model_DownloadLink
     */
    public function testSaveDownloadLinkFile()
    {
        $downloadLinkData = $this->_getDownloadLinkData();
        $result = $this->_getUit()->saveDownloadLink($downloadLinkData);
        
        $this->assertTrue(! empty($result['url']));
        $this->assertEquals($this->_getDownloadUrl($result['id']), $result['url']);
        $this->assertEquals(0, $result['access_count']);
        
        return $result;
    }

    protected function _getDownloadUrl($id)
    {
        return Tinebase_Core::getUrl() . '/download/show/' . $id;
    }
    
    /**
     * testSaveDownloadLinkDirectory
     *
     * @return array Filemanager_Model_DownloadLink
     */
    public function testSaveDownloadLinkDirectory()
    {
        $downloadLinkData = $this->_getDownloadLinkData();
        $result = $this->_getUit()->saveDownloadLink($downloadLinkData);
        
        $this->assertTrue(! empty($result['url']));
        $this->assertEquals($this->_getDownloadUrl($result['id']), $result['url']);
        
        return $result;
    }
    
    /**
     * get download link data
     * 
     * @param string $nodeType
     * @return array
     * @throws Tinebase_Exception_InvalidArgument
     */
    protected function _getDownloadLinkData($nodeType = Tinebase_Model_Tree_FileObject::TYPE_FILE)
    {
        // create node first
        if ($nodeType === Tinebase_Model_Tree_FileObject::TYPE_FILE) {
            $node = $this->testCreateFileNodeWithTempfile();
        } else if ($nodeType === Tinebase_Model_Tree_FileObject::TYPE_FOLDER) {
            $node = $this->testCreateContainerNodeInPersonalFolder();
        } else {
            throw new Tinebase_Exception_InvalidArgument('only file and folder nodes are supported');
        }
        
        return array(
            'node_id'       => $node['id'],
            'expiry_date'   => Tinebase_DateTime::now()->addDay(1)->toString(),
            'access_count'  => 7,
        );
    }
    
    /**
     * testGetDownloadLink
     */
    public function testGetDownloadLink()
    {
        $downloadLink = $this->testSaveDownloadLinkFile();
        
        $this->assertEquals($downloadLink, $this->_getUit()->getDownloadLink($downloadLink['id']));
    }
    
    /**
     * testSearchDownloadLinks
     */
    public function testSearchDownloadLinks()
    {
        $downloadLink = $this->testSaveDownloadLinkFile();
        $filter = array(array(
            'field'     => 'id',
            'operator'  => 'equals',
            'value'     => $downloadLink['id']
        ));
        $result = $this->_getUit()->searchDownloadLinks($filter, array());
        
        $this->assertEquals(1, $result['totalcount']);
    }

    /**
     * testDeleteDownloadLinks
     */
    public function testDeleteDownloadLinks()
    {
        $downloadLink = $this->testSaveDownloadLinkFile();

        $this->_getUit()->deleteDownloadLinks(array($downloadLink['id']));
        try {
            Filemanager_Controller_DownloadLink::getInstance()->get($downloadLink['id']);
            $this->fail('link should have been deleted');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof Tinebase_Exception_NotFound);
        }
    }

    /**
     * @see 0012788: allow acl for all folder nodes
     */
    public function testNodeAclAndPathResolving()
    {
        $this->testCreateFileNodes();

        // search folders + assert grants
        $sharedRoot = '/' . Tinebase_FileSystem::FOLDER_TYPE_SHARED;
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => $sharedRoot
        ));
        $result = $this->_getUit()->searchNodes($filter, array());

        self::assertEquals(1, $result['totalcount']);
        $node = $result['results'][0];
        self::assertEquals('/shared/testcontainer', $node['path'], 'no path found in node: ' . print_r($node, true));
        $this->_assertGrantsInNode($node);

        // search files + assert grants
        $filter = array(array(
            'field'    => 'path',
            'operator' => 'equals',
            'value'    => $node['path']
        ));
        $result = $this->_getUit()->searchNodes($filter, array());
        self::assertEquals(2, $result['totalcount'], 'no files found in path: ' . print_r($result, true));
        $file1Node = $result['results'][0];
        self::assertContains('/shared/testcontainer/file', $file1Node['path'], 'no path found in node: ' . print_r($file1Node, true));
        $this->_assertGrantsInNode($file1Node);

        $file2Node = $this->_getUit()->getNode($result['results'][1]['id']);
        self::assertContains('/shared/testcontainer/file', $file2Node['path'], 'no path found in node: ' . print_r($file2Node, true));
        $this->_assertGrantsInNode($file2Node);
    }

    /**
     * check if account grants are resolved correctly
     *
     * @param $nodeArray
     */
    protected function _assertGrantsInNode($nodeArray)
    {
        self::assertEquals(2, count($nodeArray['grants']));
        self::assertTrue(is_array($nodeArray['grants'][0]['account_name']), 'account_name is not resolved');
        self::assertEquals(true, count($nodeArray['account_grants']['adminGrant']));
    }

    public function testSetNodeAcl()
    {
        $node = $this->testCreateContainerNodeInSharedFolder();
        $node['grants'] = Tinebase_Model_Grants::getPersonalGrants(Tinebase_Core::getUser())->toArray();
        $result = $this->_getUit()->saveNode($node);

        self::assertEquals(1, count($result['grants']), print_r($result['grants'], true));
        self::assertEquals(Tinebase_Acl_Rights::ACCOUNT_TYPE_USER, $result['grants'][0]['account_type']);

        return $node;
    }

    public function testRemoveNodeAclTopLevel()
    {
        $node = $this->testSetNodeAcl();
        $node['grants'] = null;
        $result = $this->_getUit()->saveNode($node);

        self::assertEquals(1, count($result['grants']), 'it is not allowed to remove top level node grants - '
            . print_r($result['grants'], true));
    }

    public function testRemoveNodeAclChildLevel()
    {
        $node = $this->testCreateContainerNodeInSharedFolder();
        // create child folder node
        $testPath = $node['path'] . '/child';
        $child = $this->_getUit()->createNode($testPath, Tinebase_Model_Tree_FileObject::TYPE_FOLDER, null, false);

        $child['grants'] = Tinebase_Model_Grants::getPersonalGrants(Tinebase_Core::getUser())->toArray();
        $child['acl_node'] = $child['id'];
        $child = $this->_getUit()->saveNode($child);

        self::assertEquals(1, count($child['grants']), 'node should have only personal grants - '
            . print_r($child['grants'], true));

        $child['acl_node'] = null;
        $childWithoutPersonalGrants = $this->_getUit()->saveNode($child);

        self::assertEquals(2, count($childWithoutPersonalGrants['grants']), 'node should have parent grants again - '
            . print_r($childWithoutPersonalGrants['grants'], true));
    }

    public function testRecursiveFilter()
    {
        $folders = $this->testCreateDirectoryNodesInPersonal();
        $prefix = Tinebase_FileSystem::getInstance()->getApplicationBasePath('Filemanager') . '/folders';

        $i = 0;
        foreach($folders as $folder) {
            $path = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $folder . '/test.txt');
            $handle = Tinebase_FileSystem::getInstance()->fopen($path->statpath, 'w');
            $this->assertTrue(is_resource($handle), 'fopen did not return resource');

            $written = fwrite($handle, 'RecursiveTest' . (++$i));
            $this->assertEquals(14, $written, 'failed to write 14 bytes to ' . $folder . '/test.txt');

            $result = Tinebase_FileSystem::getInstance()->fclose($handle);
            $this->assertTrue($result, 'fclose did not return true');
        }

        $path = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer';
        $this->_rmDir[] = $path;
        $paths = array($path . '/dir1/test.txt', $path . '/dir2/test.txt');
        Tinebase_TransactionManager::getInstance()->commitTransaction($this->_transactionId);
        $this->_transactionId = Tinebase_TransactionManager::getInstance()->startTransaction(Tinebase_Core::getDb());

        $result = $this->_getUit()->searchNodes(array(
            array(
                'field'    => 'recursive',
                'operator' => 'equals',
                'value'    => 'true'
            ),
            array(
                'field'    => 'query',
                'operator' => 'contains',
                'value'    => 'RecursiveTe'
            ),
        ), null);

        $this->assertEquals(2, $result['totalcount'], 'did not find expected 2 files: ' . print_r($result, true));
        foreach($result['results'] as $result) {
            $this->assertTrue(in_array($result['path'], $paths), 'result doesn\'t match expected paths: ' . print_r($result, true) . print_r($paths, true));
        }
    }

    protected function _statPaths($_paths)
    {
        $fileSystem = Tinebase_FileSystem::getInstance();
        $result = array();

        /**
         * @var  string $key
         * @var  Tinebase_Model_Tree_Node_Path $path
         */
        foreach($_paths as $key => $path) {
            $result[$key] = $fileSystem->stat($path->statpath);
        }

        return $result;
    }

    protected function _assertRevisionProperties(array $_nodes, array $_defaultResult, array $_resultMap = array())
    {
        /**
         * @var string $key
         * @var Tinebase_Model_Tree_Node $node
         */
        foreach($_nodes as $key => $node) {
            $actual = $node->xprops(Tinebase_Model_Tree_Node::XPROPS_REVISION);
            if (!empty($actual) && isset($actual[Tinebase_Model_Tree_Node::XPROPS_REVISION_NODE_ID])) {
                unset($actual[Tinebase_Model_Tree_Node::XPROPS_REVISION_NODE_ID]);
            }
            $expected = isset($_resultMap[$key]) ? $_resultMap[$key] : $_defaultResult;
            static::assertTrue($expected == $actual, 'node revisions don\'t match for node: ' . $node->name . ' expected: ' . print_r($expected, true) . ' actual: ' . print_r($node->xprops(Tinebase_Model_Tree_Node::XPROPS_REVISION), true));
        }
    }

    protected function _setRevisionProperties(Tinebase_Model_Tree_Node $_node, array $_properties)
    {
        foreach($_properties as $key => $value) {
            $_node->xprops(Tinebase_Model_Tree_Node::XPROPS_REVISION)[$key] = $value;
        }
        $_node->xprops(Tinebase_Model_Tree_Node::XPROPS_REVISION)[Tinebase_Model_Tree_Node::XPROPS_REVISION_NODE_ID] =
            $_node->getId();
    }

    public function testSetRevisionSettings()
    {
        $fileSystem = Tinebase_FileSystem::getInstance();
        $prefix = $fileSystem->getApplicationBasePath('Filemanager') . '/folders';

        $this->testCreateDirectoryNodesInPersonal();
        $path = '/' . Tinebase_FileSystem::FOLDER_TYPE_PERSONAL . '/' . Tinebase_Core::getUser()->accountLoginName . '/testcontainer';
        $this->_getUit()->createNodes(array($path . '/dir1/subdir11', $path . '/dir1/subdir12'), Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);
        $this->_getUit()->createNodes(array($path . '/dir2/subdir21', $path . '/dir2/subdir22'), Tinebase_Model_Tree_FileObject::TYPE_FOLDER, array(), false);


        // NONE of them have revision properties
        $paths = array(
            'dir1'          => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir1'),
            'subDir11'      => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir1/subdir11'),
            'subDir12'      => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir1/subdir12'),
            'dir2'          => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2'),
            'subDir21'      => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir21'),
            'subDir22'      => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir22'),
            'testContainer' => Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path),
        );

        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, array());



        // ADD revision properties to the DIR1 subtree
        $dir1TreeRevisionProperties = array(
            Tinebase_Model_Tree_Node::XPROPS_REVISION_MONTH => 1,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_NUM   => 2,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_ON    => true
        );
        $this->_setRevisionProperties($nodes['dir1'], $dir1TreeRevisionProperties);

        $this->_getUit()->saveNode($nodes['dir1']->toArray());

        $fileSystem->clearStatCache();
        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, array(), array(
            'dir1'      => $dir1TreeRevisionProperties,
            'subDir11'  => $dir1TreeRevisionProperties,
            'subDir12'  => $dir1TreeRevisionProperties
        ));



        // MOVE DIR1 subtree below SUBDIR21 => nothing should change
        $fileSystem->rename($paths['dir1']->statpath, $paths['subDir21']->statpath . '/tmp');

        $fileSystem->clearStatCache();
        $paths['dir1']     = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir21/tmp');
        $paths['subDir11'] = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir21/tmp/subdir11');
        $paths['subDir12'] = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir21/tmp/subdir12');
        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, array(), array(
            'dir1'      => $dir1TreeRevisionProperties,
            'subDir11'  => $dir1TreeRevisionProperties,
            'subDir12'  => $dir1TreeRevisionProperties
        ));



        // ADD revision properties to the SUBDIR22 subtree
        $subdir22TreeRevisionProperties = array(
            Tinebase_Model_Tree_Node::XPROPS_REVISION_MONTH => 2,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_NUM   => 3,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_ON    => true
        );
        $this->_setRevisionProperties($nodes['subDir22'], $subdir22TreeRevisionProperties);

        $this->_getUit()->saveNode($nodes['subDir22']->toArray());

        // MOVE DIR1 from SUBDIR21 to SUBDIR22 => they should still not change!
        $fileSystem->rename($paths['dir1']->statpath, $paths['subDir22']->statpath . '/tmp');

        $fileSystem->clearStatCache();
        $paths['dir1']     = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir22/tmp');
        $paths['subDir11'] = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir22/tmp/subdir11');
        $paths['subDir12'] = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir22/tmp/subdir12');
        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, array(), array(
            'subDir22'  => $subdir22TreeRevisionProperties,
            'dir1'      => $dir1TreeRevisionProperties,
            'subDir11'  => $dir1TreeRevisionProperties,
            'subDir12'  => $dir1TreeRevisionProperties
        ));



        // MOVE subDir11 to subDir21 => should change to empty
        $fileSystem->rename($paths['subDir11']->statpath, $paths['subDir21']->statpath . '/tmp');

        $fileSystem->clearStatCache();
        $paths['subDir11'] = Tinebase_Model_Tree_Node_Path::createFromPath($prefix . $path . '/dir2/subdir21/tmp');
        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, array(), array(
            'subDir22'  => $subdir22TreeRevisionProperties,
            'dir1'      => $dir1TreeRevisionProperties,
            'subDir12'  => $dir1TreeRevisionProperties
        ));



        // reset properties for whole tree
        $testContainerRevisionProperties = array(
            Tinebase_Model_Tree_Node::XPROPS_REVISION_MONTH => 6,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_NUM   => 5,
            Tinebase_Model_Tree_Node::XPROPS_REVISION_ON    => false
        );
        $this->_setRevisionProperties($nodes['testContainer'], $testContainerRevisionProperties);

        $this->_getUit()->saveNode($nodes['testContainer']->toArray());

        $fileSystem->clearStatCache();
        $nodes = $this->_statPaths($paths);
        $this->_assertRevisionProperties($nodes, $testContainerRevisionProperties);
    }

    public function testGetFolderUsage()
    {
        $result = $this->testCreateFileNodeWithTempfile();

        $usageInfo = $this->_getUit()->getFolderUsage($result['parent_id']);
        $userId = Tinebase_Core::getUser()->contact_id;

        $this->assertEquals(17, $usageInfo['type']['txt']['size']);
        $this->assertEquals(17, $usageInfo['type']['txt']['revision_size']);
        $this->assertEquals(17, $usageInfo['createdBy'][$userId]['size']);
        $this->assertEquals(17, $usageInfo['createdBy'][$userId]['revision_size']);
        $this->assertEquals($userId, $usageInfo['contacts'][0]['id']);
    }
}
