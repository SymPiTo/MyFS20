<?php
/* --------------------------------------------------------------------------- 
  TRAITS: MyHelper
...............................................................................
        
  * LogErrorToFile
  * MyLogger
  * SendDebug

  * DecodeUTF8
 
-------------------------------------------------------------------------------*/
trait LogErrorToFile
{
    /**
     * Ergänzt SendDebug um die Möglichkeit Objekte und Array auszugeben.
     *
     * @access protected
     * @param string $Message Nachricht für Data.
     * @param WebSocketFrame|mixed $Data Daten für die Ausgabe.
     * @return int $Format Ausgabeformat für Strings.
     */
        protected function ModErrorLog($ModName, $text, $array){
        {
            $path = "/home/pi/pi-share/"; 
            $file=$ModName.".log";
            
            $datum = date("d.m.Y");
            $uhrzeit = date("H:i");
            $logtime = $datum." - ".$uhrzeit." Uhr";
            if (!$array){

                    $array = '-';
            }

            //prüfen, ob file vorhanden.
            //if (file_exists($path.$filename)) {



                    if(($FileHandle = fopen($path.$file, "a")) === false) {
                            //SetValue($ID_OutEnabled, false);
                            Exit;
                    }
                    if (is_array($array)){
                            //$comma_seperated=implode("\r\n",$array);
                            $comma_seperated=print_r($array, true);
                    }
                    else {
                            $comma_seperated=$array;
                    }
                    fwrite($FileHandle, $logtime.": ".$text.": ");
                    fwrite($FileHandle, $comma_seperated."\r\n");
                    fclose($FileHandle);
           //}
        }
    }
}

/**
 * Helper class for the debug output.
 */
trait DebugHelper {
    #------------------------------------------------------------------------------# 
    #  Function: SendDebug                                                         #
    #..............................................................................#
    #  Beschreibung:                                                               #
    #      Adds functionality to serialize arrays and objects.                     #
    #..............................................................................#
    #  Parameters:  $msg    Title of the debug message.                            #
    #               $data   Data output.                                           #
    #               $format Output format.                                         #                              
    #..............................................................................#
    # Returns:     none                                                            #
    #------------------------------------------------------------------------------#
    protected function SendDebug($msg, $data, $format = 0)
    {
        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg.':'.$key, $value, 1);
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg.':'.$key, $value, 0);
            }
        } elseif (is_bool($data)) {
            parent::SendDebug($msg, ($data ? 'TRUE' : 'FALSE'), 0);
        } else {
            parent::SendDebug($msg, $data, $format);
        }
    }
}


trait NMapHelper {
    #------------------------------------------------------------------------------# 
    #  Function: checkPortState                                                    #
    #..............................................................................#
    #   Beschreibung:                                                              #
    #         prüft ob ein bestimmter Port einer TCP-Adresse offen ist             #
    #..............................................................................#
    #  Parameters:                                                                 #
    #          $ip     = string '192.168.178.28'                                   #
    #          $port   = string '8888'                                             #
    #          %type   = bool if true => result as bool                            #
    #                         if false => result as string                         #                              
    #..............................................................................#
    # Returns:    true  => Port is open                                            #
    #             false => Port is closed                                          #
    #------------------------------------------------------------------------------#
    protected function checkPortState($ip, $port, $type=true){
      
        $cmd = "sudo nmap -p T:".$port." ".$ip;
        // linux Befehl ausführen mit  ``'
        $output = `$cmd`;
        $posOpen = strpos($output, "open");
        $posClose = strpos($output, "closed");
        //open Textstring gefunden
        if ($posOpen != false) {
            if ($type) {
                $result = true;
            }
            else {
                $result = "open";
            }
        }
        elseif ($posClose != false){
            if ($type){
                $result = false; //closed Textstring gefunden
              }
              else {
                $result = "closed";
              }
        }
        else {
            $result = false;
        }
        return $result;
    }    

    protected function checkHost($ip, $type=true) {
        $cmd = "sudo nmap -sn ".$ip;
        // linux Befehl ausführen mit  ``'
        $output = `$cmd`;
        $up = strpos($output, "up");
        $down = strpos($output, "down");

        if ($up != false) {
            if ($type) {
                $result = true;
            }
            else {
                $result = "on";
            }
        }
        elseif ($down != false){
            if ($type){
                $result = false; //closed Textstring gefunden
              }
              else {
                $result = "off";
              }
        }
        else {
            $result = false;
        }
        return $result;
    }
}


trait ProfileHelper {
    #---------------------------------------------------------------------------------------# 
    #  Function: RegisterProfile                                                            #
    #.......................................................................................#
    #   Beschreibung:                                                                       #
    #        Create the profile for the given type, values and associations.                #
    # ..................................................................................... #
    #  Parameters:                                                                          #
    #    * @param string $vartype      Type of the variable.                                #
    #                                  0=bool, 1=Integer, 2=Float, 3=String                 #
    #    * @param string $name         Profil name.                                         #
    #    * @param string $icon         Icon to display.                                     #
    #    * @param string $prefix       Variable prefix.                                     #
    #    * @param string $suffix       Variable suffix.                                     #
    #    * @param int    $minvalue     Minimum value.                                       #
    #    * @param int    $maxvalue     Maximum value.                                       #
    #    * @param int    $stepsize     Increment.                                           #
    #    * @param int    $digits       Decimal places.                                      #
    #    * @param array  $associations Associations of the values.[key, value,icon,color]   #                                 #                              
    #.......................................................................................#
    # Returns:    none                                                                      #
    #---------------------------------------------------------------------------------------#
    #   $associations = [
    #    [0, $this->Translate('very dry'), '', 0xFF0000],
    #    [1, $this->Translate('dry'), '', 0x00FF00],
    #    [2, $this->Translate('water plant'), '', 0x00FF00],
    #    [3, $this->Translate('enough water'), '', 0x00FF00],
    #   ];
    #   $name = "Heat.Status";
    #   $vartype = 1;
    #   $icon = NULL;
    #   $prefix = NULL;
    #   $suffix = NULL;
    #   $minValue = 0;
    #   $maxValue = 4;
    #   $stepSize = 1;
    #   $digits = NULL;
    protected function RegisterProfile($vartype, $name, $icon, $prefix = '', $suffix = '', $minvalue = 0, $maxvalue = 0, $stepsize = 0, $digits = 0, $associations = null)
    {
        if (!IPS_VariableProfileExists($name)) {
            switch ($vartype) {
                case VARIABLETYPE_BOOLEAN:
                    $this->RegisterProfileBoolean($name, $icon, $prefix, $suffix, $associations);
                    break;
                case VARIABLETYPE_INTEGER:
                    $this->RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case VARIABLETYPE_FLOAT:
                    $this->RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $associations);
                    break;
                case VARIABLETYPE_STRING:
                    $this->RegisterProfileString($name, $icon);
                    break;
            }
        }
        return $name;
    }
    /**
     * Create the profile for the given type with the passed name.
     *
     * @param string $name    Profil name.
     * @param string $vartype Type of the variable.
     */
    protected function RegisterProfileType($name, $vartype)
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $vartype);
        } else {
            $profile = IPS_GetVariableProfile($name);
            if ($profile['ProfileType'] != $vartype) {
                throw new Exception('Variable profile type does not match for profile '.$name);
            }
        }
    }
    /**
     * Create a profile for boolean values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     * @param array  $asso   Associations of the values.
     */
    protected function RegisterProfileBoolean($name, $icon, $prefix, $suffix, $asso)
    {
        $this->RegisterProfileType($name, vtBoolean);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for integer values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param int    $digits   Decimal places.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_INTEGER);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);
        /* Not correct for icon visuality (0-100)
        if (($asso !== null) && (count($asso) !== 0)) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        */
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for float values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param int    $digits   Decimal places.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_FLOAT);
        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileDigits($name, $digits);
        if (($asso !== null) && (count($asso) !== 0)) {
            $minvalue = 0;
            $maxvalue = 0;
        }
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);
        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $ass[1], $ass[2], $ass[3]);
            }
        }
    }
    /**
     * Create a profile for string values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     */
    protected function RegisterProfileString($name, $icon, $prefix, $suffix)
    {
        $this->RegisterProfileType($name, IPSVarType::VARIABLETYPE_STRING);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileIcon($name, $icon);
    }

        
    #------------------------------------------------------------------------------# 
    #  Function: UnregisterProfile                                                 #
    #..............................................................................#
    #  Legt ein Unterverzeichnis an                                                #
    #..............................................................................#
    #  Parameters:  $Name                                                          #                              
    #..............................................................................#
    # Returns:     none                                                            #
    #------------------------------------------------------------------------------#
    protected function UnregisterProfile(string $Name){
        if (IPS_VariableProfileExists($Name)) {
           IPS_DeleteVariableProfile($Name);
        }   
    }	
}
/**
 * Helper class to create timer and events.
 */
trait TimerHelper
{
    /**
     * Create a cyclic timer.
     *
     * @param string $ident  Name and ident of the timer.
     * @param int    $hour   Start hour.
     * @param int    $minute Start minute.
     * @param int    $second Start second.
     * @param int    $script Script ID.                 _PREFIX__Scriptname($_IPS[\'TARGET\'])
     * @param bool   $active True to activate the timer, oterwise false.
     */
    protected function RegisterCyclicTimer($ident, $hour, $minute, $second, $script, $active)
    {
        $id = @$this->GetIDForIdent($ident);
        $name = $ident;
        if ($id && IPS_GetEvent($id)['EventType'] != 1) {
            IPS_DeleteEvent($id);
            $id = 0;
        }
        if (!$id) {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $ident);
        }
        IPS_SetName($id, $name);
        // IPS_SetInfo($id, "Update Timer");
        // IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $script);
        if (!IPS_EventExists($id)) {
            throw new Exception("Ident with name $ident is used for wrong object type");
        }
        //IPS_SetEventCyclic($id, 0, 0, 0, 0, 0, 0);
        IPS_SetEventCyclicTimeFrom($id, $hour, $minute, $second);
        IPS_SetEventActive($id, $active);
    }
}




/**
 * Trait welcher Objekt-Eigenschaften in den Instance-Buffer schreiben und lesen kann.
 */
    #------------------------------------------------------------------------------# 
    #  Function: BuffHelper                                                        #
    #..............................................................................#
    #   Beschreibung:                                                              #
    #         schreiben und lesen von Objekt Eigenschafen in den Instanz Buffer    #
    #         Syntax $this->Variable
    #..............................................................................#
    #  Parameters:  Get ->  Property Name             ($x = $this->VariableName)   #
    #               Set ->  Property Name, Wert       $this-VariableName = "fhsk"  #                                                        
    #..............................................................................#
    # Returns:                                             #
    #                                                     #
    #------------------------------------------------------------------------------#
trait BuffHelper {
    # Wert einer Eigenschaft aus den InstanceBuffer lesen.
    public function __get($name) {
        return unserialize($this->GetBuffer($name));
    }

    # Wert einer Eigenschaft in den InstanceBuffer schreiben.
    public function __set($name, $value) {
        $Data = serialize($value);
        $this->SetBuffer($name, $Data);
    }
}


trait VersionHelper{		
    #----------------------------------------------------------------------------#
    #   Function: GetIPSVersion                                                  #
    #............................................................................#
    #   Beschreibung: gibt die instalierte IPS Version zurück                    #
    #............................................................................#
    #   Parameters:   none                                                       #
    #    ........................................................................#
    #    Returns:                                                                # 
    #        $ipsversion (floatint)                                              #
    #----------------------------------------------------------------------------#
        protected function GetIPSVersion(){
            $ipsversion = floatval(IPS_GetKernelVersion());
            if ($ipsversion < 4.1) // 4.0
            {
                $ipsversion = 0;
            } elseif ($ipsversion >= 4.1 && $ipsversion < 4.2) // 4.1
            {
                $ipsversion = 1;
            } elseif ($ipsversion >= 4.2 && $ipsversion < 4.3) // 4.2
            {
                $ipsversion = 2;
            } elseif ($ipsversion >= 4.3 && $ipsversion < 4.4) // 4.3
            {
                $ipsversion = 3;
            } elseif ($ipsversion >= 4.4 && $ipsversion < 5) // 4.4
            {
                $ipsversion = 4;
            } elseif ($ipsversion >= 4.4 && $ipsversion < 6) // 5
            {
                $ipsversion = 5;
            }
            else {
                $ipsversion = 6;
            }
    
            return $ipsversion;
        }
}

trait ModuleHelper{
    #-------------------------------------------------------------------------------#
    #    Function: ModuleUp                                                         #
    #...............................................................................#
    #    prüft ob Kernelhochgefahren und Modul eingeschaltet                        #
    #    wird benutzt um Timer nur einzuschaltenwen true;                           #
    # ..............................................................................#
    #    Parameters:                                                                #
    #        $ModuleSwitchedOn -> true/false                                        #
    #...............................................................................# 
    #   Returns:                                                                    #
    #        true/false                                                             #
    #-------------------------------------------------------------------------------#
    protected function ModuleUp($ModuleSwitchedOn){
        //Only call this in READY state. On startup the WebHook instance might not be available yet
        if ((IPS_GetKernelRunlevel() == KR_READY) & ($ModuleSwitchedOn)){
        #Kernel ist hochgefahren und Module ist eingeschaltet 
        return true;
        }
        else{
            return false;
        }
    }

    #------------------------------------------------------------------------------------------------------# 
    #  Function: RegisterCategory                                                                          #
    #......................................................................................................#
    #  Legt ein Unterverzeichnis an                                                                        #
    #......................................................................................................#
    #  Parameters:      $ident                                                                             #
    #                   $catName                                                                           #         
    #......................................................................................................#
    #  Returns:     none                                                                                   #
    #------------------------------------------------------------------------------------------------------#
    private function RegisterCategory($ident, $catName ) {
        $KategorieID = @IPS_GetCategoryIDByName($catName, $this->InstanceID);
        if ($KategorieID === false){
            // Anlegen einer neuen Kategorie mit dem Namen $catName
            $CatID = IPS_CreateCategory();       // Kategorie anlegen
            IPS_SetName($CatID, $catName); // Kategorie benennen
             IPS_SetIdent($CatID, $ident);
            IPS_SetParent($CatID, $this->InstanceID); // Kategorie einsortieren unterhalb der der Instanz
        }
        return $KategorieID;
    }

    protected function CreateCategoryByIdent($Parentid, $ident, $name) {
             $cid = @IPS_GetObjectIDByIdent($ident, $Parentid);
             if($cid === false) {
                     $cid = IPS_CreateCategory();
                     IPS_SetParent($cid, $Parentid);
                     IPS_SetName($cid, $name);
                     IPS_SetIdent($cid, $ident);
             }
             return $cid;
    } 


    
    #-------------------------------------------------------------------------------------------------------# 
    #  Function: Create Link                                                                                #
    #.......................................................................................................#
    #  Beschreibung:  Legt ein Link zu einem Object an                                                      #
    #.......................................................................................................#
    #  Parameters: $Name                                                                                    #
    #              $ParentID                                                                                #
    #              $LinkedVariableID                                                                        #                           
    #.......................................................................................................#
    #  Returns:    none                                                                                     #
    #-------------------------------------------------------------------------------------------------------#
    protected function CreateLink(string $Name,  $ParentID,  $LinkedVariableID){
        $LinkID = @IPS_GetLinkIDByName($Name, $ParentID);
        if ($LinkID === false){
            // Anlegen eines neuen Links mit dem Namen "Regenerfassung"
            $LinkID = IPS_CreateLink();             // Link anlegen
            IPS_SetName($LinkID, $Name); // Link benennen
            IPS_SetParent($LinkID, $ParentID); // Link einsortieren unter dem Objekt mit der ID "12345"
            IPS_SetLinkTargetID($LinkID, $LinkedVariableID);    // Link verknüpfen
        }
    }


}

trait EventHelper{
    #----------------------------------------------------------------------------------------------# 
    #  Function: RegisterVarEvent                                                                  #
    #..............................................................................................#
    # Beschreibung:  legt einen Event an wenn nicht schon vorhanden                                #
    #     Beispiel:                                                                                #
    #     ("Wochenplan", "SwitchTimeEvent".$this->InstanceID, 2, $this->InstanceID, 20);           #  
    #..............................................................................................#
    # Parameters:                                                                                  #  
    #  $Name        -   Name des Events                                                            #
    #  $Ident       -   Ident Name des Events                                                      #
    #  $Typ         -   Typ des Events (0=ausgelöstes 1=cyclic 2=Wochenplan)                       #
    #  $Trigger                                                                                    #
    #      0	Bei Variablenaktualisierung                                                        #
    #      1	Bei Variablenänderung                                                              #
    #      2	Bei Grenzüberschreitung. Grenzwert wird über IPS_SetEventTriggerValue festgelegt   #
    #      3	Bei Grenzunterschreitung. Grenzwert wird über IPS_SetEventTriggerValue festgelegt  #
    #      4	Bei bestimmtem Wert. Wert wird über IPS_SetEventTriggerValue festgelegt            #
    #                                                                                              #
    #  $Parent      -   ID des Parents                                                             #
    #  $Position    -   Position der Instanz                                                       #
    #..............................................................................................#
    #  Returns:       none                                                                         #
    #----------------------------------------------------------------------------------------------#
    protected function RegisterVarEvent($Name, $Ident, $Typ, $ParentID, $Position, $trigger, $var, $cmd){
        $eid =  @IPS_GetEventIDByName($Name, $ParentID);
        if($eid === false) {
            //we need to create a new one
            $EventID = IPS_CreateEvent($Typ);
            IPS_SetParent($EventID, $ParentID);
            @IPS_SetIdent($EventID, $Ident);
            IPS_SetName($EventID, $Name);
            IPS_SetPosition($EventID, $Position);
            IPS_SetEventTrigger($EventID, $trigger, $var);   //OnChange für Variable $var    
            IPS_SetEventScript($EventID, $cmd );
            IPS_SetEventActive($EventID, true);
            //IPS_SetEventTriggerValue($EventID, $limit);
            return $EventID;
        } 
        else{
            return $eid;
        }    
    }   
}

trait ScheduleHelper {
    #------------------------------------------------------------------------------------------------------------# 
    #  Function: RegisterScheduleAction                                                                          #
    #............................................................................................................#
    #  Legt eine Aktion für den Event fest                                                                       #
    #  Beispiel:                                                                                                 #
    #  ("SwitchTimeEvent".$this->InstanceID), 1, "Down", 0xFF0040, "FSSC_SetRolloDown(\$_IPS['TARGET']);");      #
    #............................................................................................................#
    #  Parameters:                                                                                               #
    #     $EventID                                                                                               #
    #     $ActionID                                                                                              #
    #     $Name                                                                                                  #
    #     $Color                                                                                                 #
    #     $Script                                                                                                #
    #............................................................................................................#
    #  Returns:    none                                                                                          #
    #------------------------------------------------------------------------------------------------------------#
    private function RegisterScheduleAction($EventID, $ActionID, $Name, $Color, $Script)
    {
            IPS_SetEventScheduleAction($EventID, $ActionID, $Name, $Color, $Script);
    }
}
