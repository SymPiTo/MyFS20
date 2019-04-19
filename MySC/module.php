<?php

 require_once(__DIR__ . "/../libs/NetworkTraits3.php");

/**
 * Title: FS20 RSU Shutter Control
  *
 * author PiTo
 * 
 * GITHUB = <https://github.com/SymPiTo/MySymCodes/tree/master/MyFS20SC>
 * 
 * Version:1.0.2018.08.21
 */
//Class: MyFS20_SC
class MyRolloShutter extends IPSModule
{
    //externe Klasse einbinden - ueberlagern mit TRAIT.
    use MyDebugHelper3;
    /* 
    _______________________________________________________________________ 
     Section: Internal Modul Funtions
     Die folgenden Funktionen sind Standard Funktionen zur Modul Erstellung.
    _______________________________________________________________________ 
     */
            
            
    
    /* ------------------------------------------------------------ 
    Function: Create  
    Create() wird einmalig beim Erstellen einer neuen Instanz und 
    neu laden der Modulesausgeführt. Vorhandene Variable werden nicht veändert, auch nicht 
    eingetragene Werte (Properties).
    Überschreibt die interne IPS_Create($id)  Funktion
   
     CONFIG-VARIABLE:
      FS20RSU_ID   -   ID des FS20RSU Modules (selektierbar).
      Time_OU      -   Zeit von Oben bis unten in Sekunden
      Time_UO      -   Zeit von Unten bis oben in Sekunden
      Time_OM      -   Zeit von Oben bis Mitte in Sekunden
      Time_UM      -   Zeit von Unten bis Mitte in Sekunden
      SunRise      -   Schalter um SunRise Event zu aktivieren
     
    STANDARD-AKTIONEN:
      FSSC_Position    -   Position (integer)
      UpDown           -   up/Down  (bool)
      Mode             -   Automatik/Manual (bool)
    ------------------------------------------------------------- */
    public function Create()
    {
	//Never delete this line!
        parent::Create();
		
	//These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.}
        
        // Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
        // Aufruf dieser Form Variable mit $Tup = $this->ReadPropertyFloat('IDENTNAME'); 
        $this->RegisterPropertyBoolean("aktiv", false);
        $this->RegisterPropertyInteger("FS20RSU_ID", 0);
        $this->RegisterPropertyInteger ("SunSet_ID", 57942);
        $this->RegisterPropertyInteger ("SunRise_ID", 11938);
        $this->RegisterPropertyFloat("Time_UO", 0.5);
        $this->RegisterPropertyFloat("Time_OU", 0.5);
        $this->RegisterPropertyFloat("Time_UM", 0.5);
        $this->RegisterPropertyFloat("Time_OM", 0.5);
        $this->RegisterPropertyBoolean("OffSetMoFr", false);
        $this->RegisterPropertyBoolean("OffSetSaSo", false);
        
         //Profile anlegen falls noch nicht vorhanden.
        $assocA[0] = "Manual";
        $assocA[1] = "Automatic";
            if (!IPS_VariableProfileExists("Rollo.Mode")) {
                IPS_CreateVariableProfile("Rollo.Mode", 0); // 0 boolean, 1 int, 2 float, 3 string,
            }
        
        $assocB[0] = "Up";
        $assocB[1] = "Down";
            if (!IPS_VariableProfileExists("Rollo.UpDown")) {
                  IPS_CreateVariableProfile("Rollo.UpDown", 0); // 0 boolean, 1 int, 2 float, 3 string,
            }
        $assocC[0] = "off";
        $assocC[1] = "on";
           if (!IPS_VariableProfileExists("Rollo.SunSet")) {
                  IPS_CreateVariableProfile("Rollo.SunSet", 0); // 0 boolean, 1 int, 2 float, 3 string,
           }

            if (!IPS_VariableProfileExists("Rollo.Position")) {
                IPS_CreateVariableProfile("Rollo.Position", 1); // 0 boolean, 1 int, 2 float, 3 string,
                IPS_SetVariableProfileDigits('Rollo.Position', 0);
                IPS_SetVariableProfileIcon('Rollo.Position', 'Jalousie');
                IPS_SetVariableProfileText('Rollo.Position', '', ' %');
                IPS_SetVariableProfileValues('Rollo.Position', 0, 100, 1);
            }
            
        //Integer Variable anlegen
        //integer RegisterVariableInteger ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $this->RegisterVariableInteger("FSSC_Position", "Position", "");
        $this->RegisterVariableInteger("FSSC_Timer", "Timer", "");   
        IPS_SetHidden($this->GetIDForIdent("FSSC_Timer"), true); //Objekt verstecken
      
        //Boolean Variable anlegen
        //integer RegisterVariableBoolean ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $this->RegisterVariableBoolean("UpDown", "Rollo Up/Down");
        $this->RegisterVariableBoolean("Mode", "Mode");
        $this->RegisterVariableBoolean("SS", "SunSet-Rise");
        
        //String Variable anlegen
        //RegisterVariableString (  $Ident,  $Name, $Profil, $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $this->RegisterVariableString("SZ_MoFr", "SchaltZeiten Mo-Fr");
        $this->RegisterVariableString("SZ_SaSo", "SchaltZeiten Sa-So");
        $this->RegisterVariableInteger("OffSetSR_MoFr", "OffSet SunRise Mo-Fr");
        $this->RegisterVariableInteger("OffSetSR_SaSo", "OffSet SunRise Sa-So");
        $this->RegisterVariableInteger("OffSetSS_MoFr", "OffSet SunSet Mo-Fr");
        $this->RegisterVariableInteger("OffSetSS_SaSo", "OffSet SunSet Sa-So");
        setvalue($this->GetIDForIdent("OffSetSR_MoFr"),"+0");
        setvalue($this->GetIDForIdent("OffSetSR_SaSo"),"+0");
        setvalue($this->GetIDForIdent("OffSetSS_MoFr"),"+0");
        setvalue($this->GetIDForIdent("OffSetSS_SaSo"),"+0");
        
        // Profile den Variablen zuordnen   
        IPS_SetVariableCustomProfile($this->GetIDForIdent("FSSC_Position"), "Rollo.Position");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("UpDown"), "Rollo.UpDown");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("Mode"), "Rollo.Mode");
        

     
        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        $this->EnableAction("FSSC_Position");
        $this->EnableAction("UpDown");
        $this->EnableAction("Mode");
            
        
        //anlegen eines Timers
        $this->RegisterTimer("LaufzeitTimer", 0, "FSSC_reset(\$_IPS['TARGET']);");


     

        //$this->RegisterEvent("Laufzeit", "LaufzeitEvent".$this->InstanceID, 1, $this->InstanceID, 22);
        //$LaufzeitEventID = $this->GetIDForIdent("LaufzeitEvent".$this->InstanceID);
        //IPS_SetEventCyclic($LaufzeitEventID, 0, 0, 0, 0, 1, 35 /* Alle 35 Sekunden */);    
        //IPS_SetEventScript($LaufzeitEventID, "FSSC_reset(\$_IPS['TARGET']);")  ;
        
        
        
    	// Anlegen des cyclic events SunRise mit ($Name, $Ident, $Typ, $Parent, $Position).
	$this->RegisterEvent("SunRiseMoFr", "SunRiseEventMoFr".$this->InstanceID, 1, $this->InstanceID, 21); 
        $SunRiseMoFrEventID = $this->GetIDForIdent("SunRiseEventMoFr".$this->InstanceID);
        // täglich, um x Uhr
        $sunrise = getvalue($this->ReadPropertyInteger("SunRise_ID"));
        $sunrise_H = date("H", $sunrise); 
        $sunrise_M = date("i", $sunrise); 
        IPS_SetEventCyclicTimeFrom($SunRiseMoFrEventID, $sunrise_H, $sunrise_M, 0);
        IPS_SetEventScript($SunRiseMoFrEventID, "FSS_SetRolloUp(\$_IPS['TARGET']);");
        
    	// Anlegen des cyclic events SunSet mit ($Name, $Ident, $Typ, $Parent, $Position)
	$this->RegisterEvent("SunSetMoFr", "SunSetEventMoFr".$this->InstanceID, 1, $this->InstanceID, 21); 
        $SunSetMoFrEventID = $this->GetIDForIdent("SunSetEventMoFr".$this->InstanceID);
        // täglich, um x Uhr
        $sunset = getvalue($this->ReadPropertyInteger("SunSet_ID"));
        $sunset_H = date("H", $sunset); 
        $sunset_M = date("i", $sunset); 
        IPS_SetEventCyclicTimeFrom($SunSetMoFrEventID, $sunset_H, $sunset_M, 0);
        IPS_SetEventScript($SunSetMoFrEventID, "FSS_SetRolloDown(\$_IPS['TARGET']);");

    	// Anlegen des cyclic events SunRise mit ($Name, $Ident, $Typ, $Parent, $Position).
	$this->RegisterEvent("SunRiseSaSo", "SunRiseEventSaSo".$this->InstanceID, 1, $this->InstanceID, 21); 
        $SunRiseSaSoEventID = $this->GetIDForIdent("SunRiseEventSaSo".$this->InstanceID);
        // täglich, um x Uhr
        $sunrise = getvalue($this->ReadPropertyInteger("SunRise_ID"));
        $sunrise_H = date("H", $sunrise); 
        $sunrise_M = date("i", $sunrise); 
        IPS_SetEventCyclicTimeFrom($SunRiseSaSoEventID, $sunrise_H, $sunrise_M, 0);
        IPS_SetEventScript($SunRiseSaSoEventID, "FSS_SetRolloUp(\$_IPS['TARGET']);");
        
    	// Anlegen des cyclic events SunSet mit ($Name, $Ident, $Typ, $Parent, $Position)
	$this->RegisterEvent("SunSetSaSo", "SunSetEventSaSo".$this->InstanceID, 1, $this->InstanceID, 21); 
        $SunSetEventSaSoID = $this->GetIDForIdent("SunSetEventSaSo".$this->InstanceID);
        // täglich, um x Uhr
        $sunset = getvalue($this->ReadPropertyInteger("SunSet_ID"));
        $sunset_H = date("H", $sunset); 
        $sunset_M = date("i", $sunset); 
        IPS_SetEventCyclicTimeFrom($SunSetEventSaSoID, $sunset_H, $sunset_M, 0);
        IPS_SetEventScript($SunSetEventSaSoID, "FSS_SetRolloDown(\$_IPS['TARGET']);");
    }
    
   /* ------------------------------------------------------------ 
     Function: ApplyChanges    
      ApplyChanges() Wird ausgeführt, wenn auf der Konfigurationsseite "Übernehmen" gedrückt wird 
      und nach dem unittelbaren Erstellen der Instanz.
     
    SYSTEM-VARIABLE:
        InstanceID - $this->InstanceID.

    EVENTS:
        SwitchTimeEvent".$this->InstanceID   -   Wochenplan (Mo-Fr und Sa-So)
        SunRiseEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunRise
        SunSetEvent".$this->InstanceID       -   cyclice Time Event jeden Tag at SunSet
    ------------------------------------------------------------- */
    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();
        
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);    
        $this->RegisterMessage($this->InstanceID, IPS_LOGMESSAGE);
        
        $this->updateSwitchTimes();
        
        $SunRiseMoFrEventID = $this->GetIDForIdent("SunRiseEventMoFr".$this->InstanceID);
        $SunSetMoFrEventID = $this->GetIDForIdent("SunSetEventMoFr".$this->InstanceID);        
        $SunRiseSaSoEventID = $this->GetIDForIdent("SunRiseEventSaSo".$this->InstanceID);
        $SunSetSaSoEventID = $this->GetIDForIdent("SunSetEventSaSo".$this->InstanceID);  
        
        $state = $this->ReadPropertyBoolean('aktiv');
        if ($state){
            IPS_SetEventActive($SunRiseMoFrEventID, true);             //Ereignis  aktivieren
            IPS_SetEventActive($SunSetMoFrEventID, true);             //Ereignis  aktivieren 
            IPS_SetEventActive($SunRiseSaSoEventID, true);             //Ereignis  aktivieren
            IPS_SetEventActive($SunSetSaSoEventID, true);             //Ereignis  aktivieren 
        }    
        else {
            IPS_SetEventActive($SunRiseMoFrEventID, false);             //Ereignis  aktivieren
            IPS_SetEventActive($SunSetMoFrEventID, false);             //Ereignis  aktivieren 
            IPS_SetEventActive($SunRiseSaSoEventID, false);             //Ereignis  aktivieren
            IPS_SetEventActive($SunSetSaSoEventID, false);             //Ereignis  aktivieren  
        }
        

    }
    
    
   /* ------------------------------------------------------------ 
      Function: RequestAction  
      RequestAction() Wird ausgeführt, wenn auf der Webfront eine Variable
      geschaltet oder verändert wird. Es werden die System Variable des betätigten
      Elementes übergeben.
     
   
    SYSTEM-VARIABLE:
      $this->GetIDForIdent($Ident)     -   ID der von WebFront geschalteten Variable
      $Value                           -   Wert der von Webfront geänderten Variable

   STANDARD-AKTIONEN:
      FSSC_Position    -   Slider für Position
      UpDown           -   Switch für up / Down
      Mode             -   Switch für Automatik/Manual
     ------------------------------------------------------------- */
    public function RequestAction($Ident, $Value) {
         switch($Ident) {
            case "FSSC_Position":
                //Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
                //Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet
                $this->setRollo($Value);

                //Neuen Wert in die Statusvariable schreiben
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case "UpDown":
                //SetValue($this->GetIDForIdent($Ident), $Value);
                if(getvalue($this->GetIDForIdent($Ident))){
                    $this->SetRolloDown();  
                }
                else{
                   $this->SetRolloUp();
                }
                break;
             case "Mode":
               $this->SetMode($Value);  
                break;
            default:
                throw new Exception("Invalid Ident");
        }
    }
    
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {

           // IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
                    
        if($Message = KR_READY){
           
        }
            
    }

    
    /*  ----------------------------------------------------------------------------------------------------------------- 
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     FSSC_XYFunktion($Instance_id, ... );
     ---------------------------------------------------------------------------------------------------------------------  */


  
 
    //-----------------------------------------------------------------------------
    /* Function: StepRolloDown
    ...............................................................................
    fährt den Rolladen Schrittweise Zu = Down
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------  */
    public function StepRolloDown(){
        FS20_DimDown($this->ReadPropertyInteger("FS20RSU_ID"));
        $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) + 6; 
        if($aktpos > 100){$aktpos = 100;}
        setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern        
    }   
    //*****************************************************************************
    /* Function: StepRolloUp
    ...............................................................................
    fährt den Rolladen Schrittweise Auf = Up
    ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function StepRolloUp(){
        FS20_DimUp($this->ReadPropertyInteger("FS20RSU_ID"));
        $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) - 6; 
        if($aktpos < 0){$aktpos = 0;}
        setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern  
    }
    //*****************************************************************************
    /* Function: SetMode
    ...............................................................................
    Setzt Automatik bzw. Manual Modus
     * Automatik aktiviert die Events
     * Manual deaktiviert die Events
      ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function SetMode(bool $mode) {
        $eid = $this->GetIDForIdent("SwitchTimeEvent".$this->InstanceID);
        if ($mode) {
           IPS_SetEventActive($eid, true); 
        } 
        else {
           IPS_SetEventActive($eid, false); 
        }
       SetValue($this->GetIDForIdent("Mode"), $mode);
    } 
    //*****************************************************************************
    /* Function: SetRolloUp
    ...............................................................................
    fährt den Rolladen auf 0% = Auf = Up
    ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function SetRolloUp() {
       //$this->SendDebug( "SetRolloUp", "Fahre Rolladen hoch", 0); 
       $Tup = $this->ReadPropertyFloat('Time_UO'); 
       FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, $Tup); 
       Setvalue($this->GetIDForIdent("UpDown"),false);
       SetValue($this->GetIDForIdent("FSSC_Timer"),time());
       $this->SetTimerInterval("LaufzeitTimer", 35000);
       $this->updateSwitchTimes();
    }   
    //*****************************************************************************
    /* Function: SetRolloDown
    ...............................................................................
    fährt den Rolladen auf 100% = Zu = Down
    ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
     public function SetRolloDown() {
       //$this->SendDebug( "SetRolloDown", "Fahre Rolladen runter", 0); 
       $Tdown = $this->ReadPropertyFloat('Time_OU'); 
       FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, $Tdown); 
       Setvalue($this->GetIDForIdent("UpDown"),true); 
       SetValue($this->GetIDForIdent("FSSC_Timer"),time());
       $this->SetTimerInterval("LaufzeitTimer", 35000);
       $this->updateSwitchTimes();
    }   
    //*****************************************************************************
    /* Function: StepRolloStop
    ...............................................................................
    Stopt die fahrt
    ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
         none
    //////////////////////////////////////////////////////////////////////////////*/
     public function SetRolloStop() {
        //$this->SendDebug( "SetRolloStop", "Rolladen anhalten", 0);
        $this->SetTimerInterval("LaufzeitTimer", 0);  
        $jetzt = time();
        $StartTime = getvalue($this->GetIDForIdent("FSSC_Timer")); 
        $Laufzeit =  $jetzt - $StartTime;  
        //$this->SendDebug( "SetRolloStop", "Laufzeit: ".$Laufzeit, 0); 
        $aktPos = getvalue($this->GetIDForIdent("FSSC_Position"));
        //if ($aktPos > 99){$aktPos = 0;}
        $direct = getvalue($this->GetIDForIdent("UpDown"));  
        if($direct){  
            FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, 0);
            Setvalue($this->GetIDForIdent("FSSC_Position"), $aktPos + ($Laufzeit * (100/$this->ReadPropertyFloat('Time_OU'))));
        }
        else{
           FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, 0); 
           Setvalue($this->GetIDForIdent("FSSC_Position"), $aktPos - ($Laufzeit * (100/$this->ReadPropertyFloat('Time_UO'))));  
        }     

    }  
    //*****************************************************************************
    /* Function: SetRollo
    ...............................................................................
    fährt den Rolladen auf 100% = Zu = Down
    ...............................................................................
    Parameters: 
     $pos -   Position des Rolladens in 0-100%
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function SetRollo($pos) {
        $lastPos = getvalue($this->GetIDForIdent("FSSC_Position"));
        //$this->SendDebug( "SetRollo", "Letzte Position: ".$lastPos , 0);
        if($pos>$lastPos){
            //runterfahren
            //Abstand ermitteln
            $dpos = $pos-$lastPos;
            //Zeit ermitteln für dpos
            
            $Tdown = $this->ReadPropertyFloat('Time_OU');
            $Tmid = $this->ReadPropertyFloat('Time_OM');

            if($dpos<51){
                $time = $dpos * ($Tmid/50);
                //$this->SendDebug( "SetRollo", "Errechnete Zeit für ".$pos."ist: ".$time, 0);
                FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, $time); 
                Setvalue($this->GetIDForIdent("UpDown"),true); 
            }
            else{
                $time = $dpos * ($Tdown/50);
                //$this->SendDebug( "SetRollo", "Errechnete Zeit für ".$pos."ist: ".$time, 0);
                FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, $time); 
                Setvalue($this->GetIDForIdent("UpDown"),true); 
            }
        }
        elseif($pos<$lastPos){
            //hochfahren
            //Abstand ermitteln
            $dpos = $lastPos-$pos;
            //Zeit ermitteln für dpos
            
            $Tup = $this->ReadPropertyFloat('Time_UO');
            $Tmid = $this->ReadPropertyFloat('Time_UM');
            if($dpos<51){
                $time = $dpos * ($Tmid/50);
                //$this->SendDebug( "SetRollo", "Errechnete Zeit für ".$pos."ist: ".$time, 0);
                FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, $time); 
                Setvalue($this->GetIDForIdent("UpDown"),false); 
            }
            else{
                $time = $dpos * ($Tup/50);
                //$this->SendDebug( "SetRollo", "Errechnete Zeit für ".$pos."ist: ".$time, 0);
                FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, $time); 
                Setvalue($this->GetIDForIdent("UpDown"),false);
            } 
            
        }
        else{
            // do nothing
        }
        SetValue($this->GetIDForIdent("FSSC_Position"), $pos);
    }

 
            
     
    
    
    
   /* _______________________________________________________________________
    * Section: Private Funtions
    * Die folgenden Funktionen sind nur zur internen Verwendung verfügbar
    *   Hilfsfunktionen
    * _______________________________________________________________________
    */  
    
    //*****************************************************************************
    /* Function: reset
    ...............................................................................
    Schreibt Aktions Zeit in Timer
    ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function reset(){
        $this->SetTimerInterval("LaufzeitTimer", 0);       
        $direct = getvalue($this->GetIDForIdent("UpDown"));  
        if($direct){
            SetValue($this->GetIDForIdent("FSSC_Position"), 100);
        }
        else{
           SetValue($this->GetIDForIdent("FSSC_Position"), 0);
        } 
    }
    
    /* ---------------------------------------------------------------------------
     Function: updateSunRise
    ...............................................................................
    
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------ */
    private function updateSwitchTimes(){
        $sunrise = getvalue($this->ReadPropertyInteger("SunRise_ID"));
        $sunset = getvalue($this->ReadPropertyInteger("SunSet_ID"));
        $OffSetSR_MoFr = getvalue($this->GetIDForIdent("OffSetSR_MoFr")) ;
        $OffSetSS_MoFr = getvalue($this->GetIDForIdent("OffSetSS_MoFr")) ;
        $OffSetSR_SaSo = getvalue($this->GetIDForIdent("OffSetSR_SaSo"));
        $OffSetSS_SaSo = getvalue($this->GetIDForIdent("OffSetSS_SaSo"));
        
        $sunriseA = date('H:i', $sunrise);
        $sunsetA = date('H:i', $sunset);
        $SunRiseMoFrEventID = $this->GetIDForIdent("SunRiseEventMoFr".$this->InstanceID);
        $SunSetMoFrEventID = $this->GetIDForIdent("SunSetEventMoFr".$this->InstanceID);        
        $SunRiseSaSoEventID = $this->GetIDForIdent("SunRiseEventSaSo".$this->InstanceID);
        $SunSetSaSoEventID = $this->GetIDForIdent("SunSetEventSaSo".$this->InstanceID);       
        
        if($this->ReadPropertyBoolean("OffSetMoFr")){
            $sunriseMoFr = date('H:i:s', strtotime($sunriseA) + $OffSetSR_MoFr *60);  
            $sunsetMoFr = date('H:i:s',  strtotime($sunsetA) + $OffSetSS_MoFr *60);   

            $sunriseMoFr_H = date("H", strtotime($sunriseMoFr)); 
            $sunriseMoFr_M = date("i", strtotime($sunriseMoFr)); 
            IPS_SetEventCyclicTimeFrom($SunRiseMoFrEventID, $sunriseMoFr_H, $sunriseMoFr_M, 0);
            $sunSetSaSo_H = date("H", strtotime($sunsetMoFr)); 
            $sunSetSaSo_M = date("i", strtotime($sunsetMoFr));
            IPS_SetEventCyclicTimeFrom($SunSetMoFrEventID, $sunSetSaSo_H, $sunSetSaSo_M, 0);
            
            
        }
        else {
            $sunriseMoFr = date('H:i', $sunrise); 
            $sunsetMoFr = date('H:i', $sunset);
             
            $sunriseMoFr_H = date("H", strtotime($sunriseMoFr)); 
            $sunriseMoFr_M = date("i", strtotime($sunriseMoFr)); 
            IPS_SetEventCyclicTimeFrom($SunRiseMoFrEventID, $sunriseMoFr_H, $sunriseMoFr_M, 0);
            $sunSetMoFr_H = date("H", strtotime($sunsetMoFr)); 
            $sunSetMoFr_M = date("i", strtotime($sunsetMoFr));
            IPS_SetEventCyclicTimeFrom($SunSetMoFrEventID, $sunSetMoFr_H, $sunSetMoFr_M, 0);
        }
        
        if($this->ReadPropertyBoolean("OffSetSaSo")){
            $sunriseSaSo = date('H:i:s', strtotime($sunriseA) +  $OffSetSR_SaSo*60);  
            $sunsetSaSo = date('H:i:s', strtotime($sunsetA) + $OffSetSS_SaSo*60);   

            $sunriseSaSo_H = date("H", strtotime($sunriseSaSo)); 
            $sunriseSaSo_M = date("i", strtotime($sunriseSaSo)); 
            IPS_SetEventCyclicTimeFrom($SunRiseSaSoEventID, $sunriseSaSo_H, $sunriseSaSo_M, 0);
            $sunSetSaSo_H = date("H", strtotime($sunsetSaSo)); 
            $sunSetSaSo_M = date("i", strtotime($sunsetSaSo));
            IPS_SetEventCyclicTimeFrom($SunSetSaSoEventID, $sunSetSaSo_H, $sunSetSaSo_M, 0);
        }
        else {
            $sunriseSaSo = date('H:i', $sunrise); 
            $sunsetSaSo = date('H:i', $sunset);  
            
            $sunriseSaSo_H = date("H", strtotime($sunriseSaSo)); 
            $sunriseSaSo_M = date("i", strtotime($sunriseSaSo)); 
            IPS_SetEventCyclicTimeFrom($SunRiseSaSoEventID, $sunriseSaSo_H, $sunriseSaSo_M, 0);
            $sunSetSaSo_H = date("H", strtotime($sunsetSaSo)); 
            $sunSetSaSo_M = date("i", strtotime($sunsetSaSo));
            IPS_SetEventCyclicTimeFrom($SunSetSaSoEventID, $sunSetSaSo_H, $sunSetSaSo_M, 0);
        }
        
        setvalue($this->GetIDForIdent("SZ_MoFr"), $sunriseMoFr." - ".$sunsetMoFr);
        setvalue($this->GetIDForIdent("SZ_SaSo"), $sunriseSaSo." - ".$sunsetSaSo);
              
    }    
        
    

 
    
    /* ----------------------------------------------------------------------------
     Function: RegisterEvent
    ...............................................................................
    legt einen Event an wenn nicht schon vorhanden
      Beispiel:
      ("Wochenplan", "SwitchTimeEvent".$this->InstanceID, 2, $this->InstanceID, 20);  
   
    ...............................................................................
    Parameters: 
      $Name        -   Name des Events
      $Ident       -   Ident Name des Events
      $Typ         -   Typ des Events (1=cyclic 2=Wochenplan)
      $Parent      -   ID des Parents
      $Position    -   Position der Instanz
    --------------------------------------------------------------------------------
    Returns:    
        none
    -------------------------------------------------------------------------------- */
    private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position)
    {
            $eid = @$this->GetIDForIdent($Ident);
            if($eid === false) {
                    $eid = 0;
            } elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
                    IPS_DeleteEvent($eid);
                    $eid = 0;
            }
            //we need to create one
            if ($eid == 0) {
                    $EventID = IPS_CreateEvent($Typ);
                    IPS_SetParent($EventID, $Parent);
                    IPS_SetIdent($EventID, $Ident);
                    IPS_SetName($EventID, $Name);
                    IPS_SetPosition($EventID, $Position);
                    IPS_SetEventActive($EventID, false);  
            }
    }
    
    //*****************************************************************************
    /* Function: RegisterScheduleAction
    ...............................................................................
     *  Legt eine Aktion für den Event fest
     * Beispiel:
     * ("SwitchTimeEvent".$this->InstanceID), 1, "Down", 0xFF0040, "FSSC_SetRolloDown(\$_IPS['TARGET']);");
    ...............................................................................
    Parameters: 
      $EventID
      $ActionID
      $Name
      $Color
      $Script
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    private function RegisterScheduleAction($EventID, $ActionID, $Name, $Color, $Script)
    {
            IPS_SetEventScheduleAction($EventID, $ActionID, $Name, $Color, $Script);
    }
    
    
    /* ----------------------------------------------------------------------------
     Function: RegisterProfile
    ...............................................................................
    Erstellt ein neues Profil und ordnet es einer Variablen zu.
    ...............................................................................
    Parameters: 
        $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype, $VarIdent, $Assoc
     * $Vartype: 0 boolean, 1 int, 2 float, 3 string,
     * $Assoc: array mit statustexte
     *         $assoc[0] = "aus";
     *         $assoc[1] = "ein";
     * RegisterProfile("Rollo.Mode", "", "", "", "", "", "", "", 0, "", $Assoc)
    ..............................................................................
    Returns:   
        none
    ------------------------------------------------------------------------------- */
    protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype,  $Assoc){
            if (!IPS_VariableProfileExists($Name)) {
                    IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
            } else {
                    $profile = IPS_GetVariableProfile($Name);
                    if ($profile['ProfileType'] != $Vartype){
                           // $this->SendDebug("Alarm.Reset:", "Variable profile type does not match for profile " . $Name, 0);
                    }
              }
            
            //IPS_SetVariableProfileIcon($Name, $Icon);
            //IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
            //IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
            //IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
            foreach ($Assoc as $key => $value) {
                IPS_SetVariableProfileAssociation($Name, $key, $value, $Icon, 0xFFFFFF);  
            }
           // IPS_SetVariableCustomProfile($this->GetIDForIdent($VarIdent), $Name);
    }		

    /* ----------------------------------------------------------------------------
     Function: GetIPSVersion
    ...............................................................................
    gibt die instalierte IPS Version zurück
    ...............................................................................
    Parameters: 
        none
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    private function GetIPSVersion()
    {
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
            } else   // 5
            {
                    $ipsversion = 5;
            }

            return $ipsversion;
    }
 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}