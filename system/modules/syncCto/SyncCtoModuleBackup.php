<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
/**
 * Defines
 */
define("OK", 1);
define("ERROR", 2);
define("WORK", 3);
define("SKIPPED", 4);

class SyncCtoModuleBackup extends BackendModule
{

    // Variablen
    protected $strTemplate = 'be_syncCto_empty';
    protected $objTemplateContent;
    // Helper Class
    protected $objSyncCtoDatabase;
    protected $objSyncCtoFiles;
    protected $objSyncCtoCallback;

    function __construct(DataContainer $objDc = null)
    {
        $this->import('BackendUser', 'User');
        parent::__construct($objDc);


        $this->objSyncCtoDatabase = SyncCtoDatabase::getInstance();
        $this->objSyncCtoFiles = SyncCtoFiles::getInstance();
        $this->objSyncCtoCallback = SyncCtoCallback::getInstance();

        $this->loadLanguageFile('tl_syncCto_backup');
    }

    /* -------------------------------------------------------------------------
     * Core Functions
     */

    /**
     * Generate page
     */
    protected function compile()
    {
        if ($this->Input->get("do") == "syncCto_backups"
                && strlen($this->Input->get("act")) != 0
                && strlen($this->Input->get("table")) != 0)
        {
            // Which table is in use
            switch ($this->Input->get("table"))
            {
                case 'tl_syncCto_backup_db':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseDbBackupPage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_restore_db':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseDbRestorePage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_backup_file':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseFileBackupPage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
                            break;
                    }
                    break;

                case 'tl_syncCto_restore_file':
                    // Which function should be used
                    switch ($this->Input->get("act"))
                    {
                        case 'start':
                            $this->parseFileRestorePage();
                            break;

                        default:
                            $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_function']);
                            break;
                    }
                    break;

                default:
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_tables']);
                    break;
            }
        }
        else
        {
            $this->parseStartPage();
        }

        $this->parseTemplate();
    }

    /**
     * Show main page of syncCto backup.
     * 
     * @param string $message - Error msg.
     */
    protected function parseStartPage($message = null)
    {
        $this->objTemplateContent = new BackendTemplate('be_syncCto_backup');
        $this->objTemplateContent->message = $message;
    }

    /**
     * Generate the pages
     */
    protected function parseTemplate()
    {
        $this->objTemplateContent->script = $this->Environment->script;
        $this->Template->content = $this->objTemplateContent->parse();
        $this->Template->script = $this->Environment->script;
    }

    /* -------------------------------------------------------------------------
     * Functions for 'Backup' and 'Restore'
     */

    /**
     * Datenbank Backup
     *
     * @return <type>
     */
    protected function parseDbBackupPage()
    {
        //- Init ---------------------------------------------------------------
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile('tl_syncCto_backup_db');
        $this->loadLanguageFile('tl_syncCto_steps');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 0;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_DB_Content");
        $arrStepPool = $this->Session->get("SyncCto_DB_StepPool");

        switch ($step)
        {
            case 0;
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 1,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['edit'],
                    "information" => "",
                    "data" => array()
                );

                //$arrStepPool = array();
                $step = 1;
                
                print_r($arrStepPool);
                exit();

            case 1:
//                // Check Table list
//                if ($this->Input->post("table_list_recommend") == "" && $this->Input->post("table_list_none_recommend") == "")
//                {
//                    $arrContenData["error"] = true;
//                    $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['no_backup_tables'];
//
//                    break;
//                }
//
//                // Merge recommend and none recommend post arrays
//                if ($this->Input->post("table_list_recommend") != "" && $this->Input->post("table_list_none_recommend") != "")
//                    $arrTablesBackup = array_merge($this->Input->post("table_list_recommend"), $this->Input->post("table_list_none_recommend"));
//                else if ($this->Input->post("table_list_recommend"))
//                    $arrTablesBackup = $this->Input->post("table_list_recommend");
//                else if ($this->Input->post("table_list_none_recommend"))
//                    $arrTablesBackup = $this->Input->post("table_list_none_recommend");
//
//                $arrStepPool["tables"] = $arrTablesBackup;

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['step1_help'],
                    "state" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['work']
                );

                break;

            case 2:
                try
                {
                    $arrStepPool["zipname"] = $this->objSyncCtoDatabase->runDump($arrStepPool["tables"], false);
                }
                catch (Exception $exc)
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $exc->getMessage();
                    $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['error'];

                    break;
                }

                break;

            case 3:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['complete_help'] . " " . $arrStepPool["zipname"];
                $arrContenData["data"][2]["html"] = "<p class='tl_help'><br />";
                $arrContenData["data"][2]["html"] .= "<a onclick='Backend.openWindow(this, 600, 235); return false;' title='In einem neuen Fenster ansehen' href='contao/popup.php?src=tl_files/syncCto_backups/database/" . $arrStepPool["zipname"] . "'>" . $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['download_backup'] . "</a>";
                $arrContenData["data"][2]["html"] .= "</p>";

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['unknown_backup_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $arrContenData["step"] = $step;

        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_db";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];

        $this->Session->set("SyncCto_DB_Content", $arrContenData);
        $this->Session->set("SyncCto_DB_StepPool", $arrStepPool);
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseDbRestorePage()
    {
        //- Init ---------------------------------------------------------------
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile('tl_syncCto_restore_db');
        $this->loadLanguageFile('tl_syncCto_steps');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 0;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_DB_Content");
        $arrStepPool = $this->Session->get("SyncCto_DB_StepPool");

        switch ($step)
        {
            case 0;
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 1,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['edit'],
                    "information" => "",
                    "data" => array()
                );

                $arrStepPool = array();
                $step = 1;

            case 1:
                echo $this->Input->post("filelist");

                if ($this->Input->post("filelist") == "")
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['no_backup_file'];

                    break;
                }

                if (!file_exists(TL_ROOT . "/" . $this->Input->post("filelist")))
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['no_backup_file'];

                    break;
                }

                $arrStepPool["SyncCto_Restore"] = $this->Input->post("filelist");

                $arrContenData["data"][1] = array(
                    "title" => $GLOBALS['TL_LANG']['tl_syncCto_steps']['step'] . " 1",
                    "description" => $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['step1_help'],
                    "state" => $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['work']
                );

                break;

            case 2:
                try
                {
                    $this->objSyncCtoDatabase->runRestore($arrStepPool["SyncCto_Restore"]);
                }
                catch (Exception $exc)
                {
                    $arrContenData["error"] = true;
                    $arrContenData["error_msg"] = $exc->getMessage();

                    break;
                }

                break;

            case 3:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['complete_help'];

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['unknown_backup_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $arrContenData["step"] = $step;

        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];

        $this->Session->set("SyncCto_DB_Content", $arrContenData);
        $this->Session->set("SyncCto_DB_StepPool", $arrStepPool);
    }

    protected function parseFileBackupPage()
    {
        //- Init ---------------------------------------------------------------
        $this->objTemplateContent = new BackendTemplate('be_syncCto_steps');
        $this->loadLanguageFile('tl_syncCto_backup_file');
        $this->loadLanguageFile('tl_syncCto_steps');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
            $step = 0;
        else
            $step = intval($this->Input->get("step"));

        $arrContenData = $this->Session->get("SyncCto_File_Content");
        $arrStepPool = $this->Session->get("SyncCto_File_StepPool");

        switch ($step)
        {
            case 0;
                $arrContenData = array(
                    "error" => false,
                    "error_msg" => "",
                    "refresh" => true,
                    "finished" => false,
                    "step" => 1,
                    "url" => "contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=start",
                    "start" => microtime(true),
                    "headline" => $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['edit'],
                    "information" => "",
                    "data" => array()
                );

                $arrStepPool = array();

                // Check sync. typ
                if (strlen($this->Input->post('backupType')) != 0)
                {
                    if ($this->Input->post('backupType') == SYNCCTO_FULL || $this->Input->post('backupType') == SYNCCTO_SMALL)
                    {
                        $arrStepPool["syncCto_Typ"] = $this->Input->post('backupType');
                    }
                    else
                    {
                        $arrContenData["error"] = true;
                        $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['unknown_method'];
                        break;
                    }
                }
                else
                {
                    $arrStepPool["syncCto_Typ"] = SYNCCTO_SMALL;
                }

                $arrStepPool["backupName"] = $this->Input->post('backupName');
                $arrStepPool["filelist"] = $this->Input->post('filelist');

                $step = 1;

            case 1:
                if ($arrStepPool["syncCto_Typ"] == SYNCCTO_SMALL)
                {
                    //$this->objSyncCtoFiles->run
                }

                if ($arrStepPool["syncCto_Typ"] == SYNCCTO_FULL)
                {
                    
                }

                break;

            case 2:
                $arrContenData["data"][1]["state"] = $GLOBALS['TL_LANG']['tl_syncCto_backup_db']['ok'];

                $arrContenData["finished"] = true;
                $arrContenData["data"][2]["title"] = $GLOBALS['TL_LANG']['tl_syncCto_steps']['complete'];
                $arrContenData["data"][2]["description"] = $GLOBALS['TL_LANG']['tl_syncCto_restore_db']['complete_help'];

                $this->Session->set("SyncCto_DB_StepPool", "");
                break;

            default:
                $arrContenData["error"] = true;
                $arrContenData["error_msg"] = $GLOBALS['TL_LANG']['syncCto']['unknown_backup_step'];
                $arrContenData["data"] = array();
                break;
        }

        // Set templatevars and set session
        $arrContenData["step"] = $step;

        $this->objTemplateContent->goBack = $this->script . "contao/main.php?do=syncCto_backups&table=tl_syncCto_restore_db";
        $this->objTemplateContent->data = $arrContenData["data"];
        $this->objTemplateContent->step = $arrContenData["step"];
        $this->objTemplateContent->error = $arrContenData["error"];
        $this->objTemplateContent->error_msg = $arrContenData["error_msg"];
        $this->objTemplateContent->refresh = $arrContenData["refresh"];
        $this->objTemplateContent->url = $arrContenData["url"];
        $this->objTemplateContent->start = $arrContenData["start"];
        $this->objTemplateContent->headline = $arrContenData["headline"];
        $this->objTemplateContent->information = $arrContenData["information"];
        $this->objTemplateContent->finished = $arrContenData["finished"];

        $this->Session->set("SyncCto_File_Content", $arrContenData);
        $this->Session->set("SyncCto_File_StepPool", $arrStepPool);
    }

    /**
     * Datenbank wiederherstellen
     *
     * @return <type>
     */
    protected function parseFileRestorePage()
    {
        $this->loadLanguageFile('tl_syncCto_restore_file');

        if ($this->Input->get("step") == "" || $this->Input->get("step") == null)
        {
            $step = 1;
        }
        else
        {
            $step = intval($this->Input->get("step"));
        }

        $this->objTemplateContent = new BackendTemplate('be_syncCto_restore_file');

        switch ($step)
        {
            case 1:
                if ($this->Input->post("filelist") == "")
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['no_backup_file']);
                    return;
                }

                $this->Session->set("SyncCto_Restore", $this->Input->post("filelist"));

                $this->objTemplateContent->step = $step;
                $this->objTemplateContent->condition = array('1' => WORK);
                $this->objTemplateContent->refresh = true;

                return;

            case 2:
                $strRestoreFile = $this->Session->get("SyncCto_Restore");
                if ($strRestoreFile == "" || $strRestoreFile == null)
                {
                    $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['session_file_error']);
                    return;
                }

                try
                {
                    $this->objSyncCtoFiles->runRestore($strRestoreFile);
                }
                catch (Exception $exc)
                {
                    $this->objTemplateContent->step = $step - 1;
                    $this->objTemplateContent->condition = array('1' => ERROR);
                    $this->objTemplateContent->refresh = false;
                    $this->objTemplateContent->error = $exc->getMessage();
                    return;
                }

                $this->objTemplateContent->step = $step + 1;
                $this->objTemplateContent->condition = array('1' => OK);
                $this->objTemplateContent->refresh = false;
                $this->objTemplateContent->file = $strRestoreFile;
                return;

            default:
                $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_backup_step']);
                return;
        }

        $this->parseStartPage($GLOBALS['TL_LANG']['syncCto']['unknown_restore_error']);
    }

    private function saveCondition($arrCondition, $arrNewCondition)
    {
        foreach ($arrNewCondition as $key => $value)
        {
            $arrCondition[$key] = $value;
        }
        $this->Session->set("SyncCto_Condition", serialize($arrCondition));
        return $arrCondition;
    }

}

?>