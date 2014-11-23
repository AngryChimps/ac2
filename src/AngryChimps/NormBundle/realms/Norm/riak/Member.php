<?php

namespace Norm\riak;

use AC\NormBundle\core\datastore\RiakBlobDatastore;
use AC\NormBundle\core\datastore\DatastoreManager;
use Norm\riak\base\MemberBase;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Member extends MemberBase implements UserInterface, EquatableInterface {

    public static function getByPkEnabled($pk) {
        $member = self::getByPk($pk);

        if($member->status !== Member::ACTIVE_STATUS) {
            return null;
        }

        return $member;
    }
    public static function getByEmail($email) {
        $mysqlMember = \Norm\mysql\Member::getByEmail($email);

        return self::getByPk($mysqlMember->id);
    }

    public static function getByEmailEnabled($email) {
        $mysqlMember = \Norm\mysql\Member::getByEmailEnabled($email);

        return self::getByPk($mysqlMember->id);
    }

    public function getPublicArray() {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['name'] = $this->name;
        $arr['photo'] = $this->photo;
        $arr['mysql_id'] = $this->mysqlId;
        return $arr;
    }

    public function getPrivateArray() {
        $arr = $this->getPublicArray();
        $arr['email'] = $this->email;

        return $arr;
    }


    protected function createHook($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName) {
        if(!empty($autoIncrementFieldName)) {
            throw new \Exception('Auto-increment fields are not supported in the RiakBlobDatastore');
        }

        //Save to MySql
        $mysqlMember = new \Norm\mysql\Member();
        $mysqlMember->id = $this->id;
        $mysqlMember->email = $this->email;
        $mysqlMember->password = $this->password;
        $mysqlMember->name = $this->name;
        $mysqlMember->fbId = $this->fbId;
        $mysqlMember->fbAccessToken = $this->fbAccessToken;
        $mysqlMember->fname = $this->fname;
        $mysqlMember->lname = $this->lname;
        $mysqlMember->gender = $this->gender;
        $mysqlMember->locale = $this->locale;
        $mysqlMember->timezone = $this->timezone;
        $mysqlMember->dob = $this->dob;
        $mysqlMember->photo = $this->photo;
        $mysqlMember->status = $this->status;
        $mysqlMember->role = $this->role;

        $mysqlMember->save();

        // Add autoincrement id to this object as well as the $fieldData array
        $this->mysqlId = $mysqlMember->mysqlId;
        $fieldData['mysql_id'] = $this->mysqlId;

        return parent::createHook($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName);
    }

    protected function deleteHook($realm, $tableName, $primaryKeys)
    {
        $mysqlMember = \Norm\mysql\Member::getByPk($this->mysqlId);
        $mysqlMember->delete();

        return parent::deleteHook($realm, $tableName, $primaryKeys);
    }

    protected function updateHook($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys) {
        $mysqlMember = \Norm\mysql\Member::getByPk($this->mysqlId);

        $changed = $this->getChangedFields();

        if(count($changed) > 0) {
            foreach ($changed as $propertyName => $value) {
                $mysqlMember->$propertyName = $value;
            }

            $mysqlMember->save();
        }

        parent::updateHook($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys);
    }



//    protected function updateHook($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys) {
//        $bucket = $this->db->getBucket($realm, $tableName);
//        $key = $this->db->getKeyName($primaryKeys);
//        $data = json_encode(array_merge($primaryKeys, $fieldDataWithoutPrimaryKeys));
//
//        // Read back the object from Riak
//        $response = $bucket->get($key);
//
//        // Make sure we got an object back
//        if ($response->hasObject()) {
//            // Get the first returned object
//            $readObject = $response->getFirstObject();
//        }
//        else {
//            throw new \Exception('Original object not found; unable to update.');
//        }
//        $readObject->setContent($data);
//        $readObject->addIndex('email_bin', $this->email);
//        $bucket->put($readObject);
//    }



    ################################################################################################
    ###                                                                                          ###
    ###    Everything below is for Symfony's security system                                     ###
    ###                                                                                          ###
    ################################################################################################
    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool    true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        //We don't expire accounts, only credentials
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool    true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->status !== self::LOCKED_STATUS;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool    true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        //Currently we do not even expire credentials...this may change
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool    true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->status === self::ACTIVE_STATUS;
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->email !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        switch($this->role) {
            case self::USER_ROLE:
                return array('ROLE_USER');
            case self::SUPPORT_ROLE:
                return array('ROLE_USER', 'ROLE_SUPPORT');
            case self::ADMIN_ROLE:
                return array('ROLE_USER', 'ROLE_SUPPORT', 'ROLE_ADMIN');
            case self::SUPER_ADMIN_ROLE:
                return array('ROLE_USER', 'ROLE_SUPPORT', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN');
            default:
                throw new \Exception('Unknown user role');
        }

    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // we use bcrypt which stores the salt as part of the password
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}