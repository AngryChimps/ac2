<?php

namespace AngryChimps\AdminBundle\Controller;

use AC\NormBundle\core\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
/**
 * Class RiakController
 *
 * @Route("/riak")
 */
class RiakController extends Controller
{
    const PREFIX = '__norm';
    const REALM = 'riak';

    /** @var \Riak\Connection  */
    private static $conn = null;

    /**
     * @Route("/")
     * @Method({"GET"})
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('AngryChimpsAdminBundle:Riak:index.html.twig', array());
    }

    /**
     * @Route("/deleteManual/{table_name}/{ids}")
     * @Method({"GET"})
     * @param $table_name
     * @param $ids
     * @throws \Exception
     */
    public function deleteManualAction($table_name, $ids)
    {
        $primaryKeys = $this->getKeyName(explode(',', $ids));

        if(!is_array($primaryKeys)) {
            $primaryKeys = array($primaryKeys);
        }

        $bucket = $this->getObjectsBucket($table_name);
        $key = $this->getKeyName($primaryKeys);

        // Read back the object from Riak
        $response = $bucket->get($key);

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $readObject = $response->getFirstObject();
        }
        else {
            throw new \Exception('Original object not found; unable to update.');
        }

        $bucket->delete($readObject);

    }

    /**
     * @Route("/delete/{table_name}/{id}")
     * @Method({"GET"})
     * @param $table_name
     * @param $id
     */
    public function deleteAction($table_name, $id)
    {
        $class = Utils::table2class($table_name);
        $fullClass = "\\Norm\\riak\\" . $class;
        $obj = $fullClass::getByPk($id);
        $obj->delete();
    }

    /*
     * Returns the test database connection.
     *
     * @return \Riak\Connection
     */
    final protected function getConnection()
    {
        if(self::$conn === null) {
            $realms_contents = file_get_contents(__DIR__ . "/../../../../app/config/ac_norm.yml");
            $realms_parsed = yaml_parse($realms_contents);
            $datastores_contents = file_get_contents(__DIR__ . "/../../../../app/config/ac_norm_test.yml");
            $datastores_parsed = yaml_parse($datastores_contents);

            $datastoreName = $realms_parsed['realms'][self::REALM]['primary_datastore'];
            $datastoreInfo = $datastores_parsed['datastores'][$datastoreName];

            self::$conn = new \Riak\Connection($datastoreInfo['host'], $datastoreInfo['port']);
        }

        return self::$conn;
    }

    final protected function getObjectsBucket($tablename) {
        $bucketName = self::PREFIX . ':' . self::REALM . ':' . $tablename . ':objects';
        return new \Riak\Bucket($this->getConnection(), $bucketName);
    }

    protected function getKeyName($primaryKeys) {
        return implode('|', $primaryKeys);
    }

}
