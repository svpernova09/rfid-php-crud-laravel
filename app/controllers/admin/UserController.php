<?php
namespace Admin;

use View;
use Input;
use Validator;
use Redirect;
use Session;
use DateTime;
use DerpAuthController;
use DB;
use BaseController;

class UserController extends BaseController {

    /*
     * Handle basic admin functionality
    */

    public function index()
    {
        return View::make('admin/user');
    }

    public function CleanKey($key)
    {
        $int_key = intval($key);
        $clean_key = $int_key & 0x00FFFFFF;
        return $clean_key;
    }

    public function Remove($rowid)
    {
        $params = array(':rowid' => $rowid);
        $delete_user = $this->ext_conn->prepare("DELETE FROM users WHERE rowid = :rowid");
        $affected_rows = $delete_user->execute($params);

        if ($affected_rows == 1) {
            return array('status' => 'success');
        } else {
            return array('status' => 'failure', 'reason' => 'delete_failed', 'rowcount' => $affected_rows, 'errornfo' => $delete_user->errorInfo());
        }
    }

    public function create()
    {
        return View::make('admin/user-add');
    }

    public function store()
    {
        // Get and validate input
        $data = Input::all();
        $rules = array(
            'key' => array('required', 'integer'),
            'pin' => array('required', 'integer'),
            'ircName' => array('required', 'alpha_num'),
            'spokenName' => array('required', 'alpha_num'),
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return Redirect::to('admin/user/create')->withInput()->withErrors($validator);
        }
        $date = new DateTime();
        $hash = DerpAuthController::SecureThis($data['pin']);
        $addedBy = Session::get('key');
        $dateCreated = $date->getTimestamp();
        $clean_key = DerpAuthController::CleanKey($data['key']);
        if($data['isAdmin'])
        {
            $data['isAdmin'] = 1;
        } else {
            $data['isAdmin'] = 0;
        }
        if($data['isActive'])
        {
            $data['isActive'] = 1;
        } else {
            $data['isActive'] = 0;
        }
        $data = array('key'=>$clean_key,
            'hash'=>$hash,
            'ircName'=>$data['ircName'],
            'spokenName'=>$data['spokenName'],
            'addedBy' =>$addedBy,
            'dateCreated' =>$dateCreated,
            'lastLogin' =>$dateCreated,
            'isAdmin'=>$data['isAdmin'],
            'isActive'=>$data['isActive'],
            'created_at' =>$dateCreated,
            'updated_at' =>$dateCreated
        );
        var_dump($data);

        DB::table('users')->insert($data);


        echo 'Validated, need more functionality';



        /*

        $this->ext_conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $params = $data;
        $create_user = $this->ext_conn->prepare("INSERT INTO users (key,
                                                                    hash,
                                                                    ircName,
                                                                    spokenName,
                                                                    addedBy,
                                                                    dateCreated,
                                                                    isAdmin,
                                                                    lastLogin,
                                                                    isActive)
                                                            VALUES (:key,
                                                                    :hash,
                                                                    :ircName,
                                                                    :spokenName,
                                                                    :addedBy,
                                                                    :dateCreated,
                                                                    :isAdmin,
                                                                    :lastLogin,
                                                                    :isActive)");
        $create_user->execute($params);
        if ($create_user->rowCount() == 1) {
            return array('status' => 'success', 'id' => $this->ext_conn->lastInsertId());
        } else {
            return array('status' => 'failure', 'reason' => 'create_user_failed','ErrorInfo' => $create_user->errorInfo());
        }
        */
    }

    public function UpdateUser($data)
    {
        $this->ext_conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $params = $data;
        if(!empty($params[':hash'])){
            $update = $this->ext_conn->prepare("UPDATE users SET hash = :hash,
                                                            ircName = :ircName,
                                                            spokenName = :spokenName,
                                                            addedBy = :addedBy,
                                                            dateCreated = :dateCreated,
                                                            isAdmin = :isAdmin,
                                                            lastLogin = :lastLogin,
                                                            isActive = :isActive
                                             WHERE rowid = :rowid");
        } else {
            $update = $this->ext_conn->prepare("UPDATE users SET ircName = :ircName,
                                                            spokenName = :spokenName,
                                                            addedBy = :addedBy,
                                                            dateCreated = :dateCreated,
                                                            isAdmin = :isAdmin,
                                                            lastLogin = :lastLogin,
                                                            isActive = :isActive
                                             WHERE rowid = :rowid");
        }

        $update->execute($params);

        if ($update->errorCode() == '0000') {
            return array('status' => 'success', 'rowid' => $params[':rowid']);
        } else {
            return array('status' => 'failure', 'reason' => 'update_failed','error' => $update->errorInfo());
        }
    }

    public function GetThisUser($key)
    {
        $params = array(':key' => $key);
        $query = "SELECT rowid,* FROM users WHERE key = :key";
        $rows = $this->ext_conn->prepare($query);
        $rows->execute($params);
        $data = $rows->fetchall();
        // close the database connection
        $errors = $this->ext_conn->errorInfo();
        $db = NULL;
        return $data;
    }

    public function GetThisUserByRowId($rowid)
    {
        $params = array(':rowid' => $rowid);
        $query = "SELECT rowid,* FROM users WHERE rowid = :rowid";
        $rows = $this->ext_conn->prepare($query);
        $rows->execute($params);
        $data = $rows->fetchall();
        // close the database connection
        $errors = $this->ext_conn->errorInfo();
        $db = NULL;
        return $data;
    }

    public function GetAll()
    {
        $params = array();
        $query = "SELECT rowid,* FROM users WHERE 1";
        $rows = $this->ext_conn->prepare($query);
        $rows->execute($params);
        $data = $rows->fetchall();
        // close the database connection
        $errors = $this->ext_conn->errorInfo();
        $db = NULL;
        return $data;
    }
}