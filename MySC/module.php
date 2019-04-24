<?php

 //require_once(__DIR__ . "/../libs/NetworkTraits3.php");

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
    //use MyDebugHelper3;
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
        
        $this->RegisterProperties();
        $this->RegisterProfiles();
        

        //Integer Variable anlegen
        //integer RegisterVariableInteger ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $variablenID = $this->RegisterVariableInteger("FSSC_Position", "Position", "");
        IPS_SetInfo ($variablenID, "WSS");
        $this->RegisterVariableInteger("FSSC_Timer", "Timer", "");   
        IPS_SetHidden($this->GetIDForIdent("FSSC_Timer"), true); //Objekt verstecken
      
        //Boolean Variable anlegen
        //integer RegisterVariableBoolean ( string $Ident, string $Name, string $Profil, integer $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $variablenID = $this->RegisterVariableBoolean("UpDown", "Rollo Up/Down");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableBoolean("Mode", "Mode");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableBoolean("SS", "SunSetactive");
        IPS_SetInfo ($variablenID, "WSS");
        //String Variable anlegen
        //RegisterVariableString (  $Ident,  $Name, $Profil, $Position )
        // Aufruf dieser Variable mit "$this->GetIDForIdent("IDENTNAME")"
        $variablenID = $this->RegisterVariableString("SZ_MoFr", "SchaltZeiten Mo-Fr");
        IPS_SetInfo ($variablenID, "WSS");
        $variablenID = $this->RegisterVariableString("SZ_SaSo", "SchaltZeiten Sa-So");
        IPS_SetInfo ($variablenID, "WSS");
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
        $this->RegisterTimer("LaufzeitTimer", 0, "FSS_reset(\$_IPS['TARGET']);");


        
    	// Anlegen des cyclic events Up mit ($Name, $Ident, $Typ, $Parent, $Position).
	$Up_EventID = $this->RegisterEvent("Up", "Up".$this->InstanceID, 1, $this->InstanceID, 21); 
            

        
    	// Anlegen des cyclic events Down mit ($Name, $Ident, $Typ, $Parent, $Position)
	$Down_EventID = $this->RegisterEvent("Down", "Down".$this->InstanceID, 1, $this->InstanceID, 21); 
           

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
        
       // $this->RegisterMessage(0, IPS_KERNELMESSAGE);    
       // $this->RegisterMessage($this->InstanceID, KL_MESSAGE);
        
         
        if($this->ReadPropertyInteger("FS20RSU_ID") === 0){
            $this->SetStatus(204);
        } 
        else{
            $this->SetStatus(102);
        }
        setvalue($this->GetIDForIdent("SS" ), $this->ReadPropertyBoolean('SunSet'));
        
        $state = $this->ReadPropertyBoolean('aktiv');
        if ($state){
            $this->switchEvent(true);
            $this->updateSwitchTimes();
            $this->SetEventTime();
        }    
        else {
            $this->switchEvent(false);
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

          
                    
            
        switch ($Message) {
            case "204":
                

                break;

            default:
                break;
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
        if($this->ReadPropertyBoolean("negate")){
             FS20_DimUp($this->ReadPropertyInteger("FS20RSU_ID"));
            $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) - 6; 
            if($aktpos < 0){$aktpos = 0;}
            setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern  
        }else{
            if ($this->ReadPropertyInteger("Door_ID")>0){
                if(getvalue($this->GetIDForIdent("Door_ID")) === true){
                    // keine Aktion asuführen, da Tür auf ist
                }
            }
            else {
                FS20_DimDown($this->ReadPropertyInteger("FS20RSU_ID"));
                $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) + 6; 
                if($aktpos > 100){$aktpos = 100;}
                setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern     
            }
        }
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
        if($this->ReadPropertyBoolean("negate")){
                FS20_DimDown($this->ReadPropertyInteger("FS20RSU_ID"));
                $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) + 6; 
                if($aktpos > 100){$aktpos = 100;}
                setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern    
        }else{
            FS20_DimUp($this->ReadPropertyInteger("FS20RSU_ID"));
            $aktpos = getvalue($this->GetIDForIdent("FSSC_Position")) - 6; 
            if($aktpos < 0){$aktpos = 0;}
            setvalue($this->GetIDForIdent("FSSC_Position"), $aktpos ); //Stellung um 5% verändern  
        }
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
        if ($mode) {
           $this->switchEvent(true);
        } 
        else {
           $this->switchEvent(false);
        }
       SetValue($this->GetIDForIdent("Mode"), $mode);
    } 
    //*****************************************************************************
    /* Function: switchEvent
    ...............................................................................
     
     *  aktiviert die Events
     *  deaktiviert die Events
      ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function switchEvent(bool $state) {
        $UpEventID = $this->GetIDForIdent("Up".$this->InstanceID);
        $DownEventID = $this->GetIDForIdent("Down".$this->InstanceID);        

        if ($state) {        
            IPS_SetEventActive($UpEventID, true);             //Ereignis  aktivieren
            IPS_SetEventActive($DownEventID, true);             //Ereignis  aktivieren 

        } 
        else {            
            IPS_SetEventActive($UpEventID, false);             //Ereignis  aktivieren
            IPS_SetEventActive($DownEventID, false);             //Ereignis  aktivieren 
 
        }
    } 
    //*****************************************************************************
    /* Function: SetEventTime()
    ...............................................................................
     
     *  Wert aus den Variablen SZ_SaSo und SZ_MoFr holen abhängig vom Wochentag und
     *  in die Timer Events übertrgagen Schaltzeit setzen
      ...............................................................................
    Parameters: 
        none
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function SetEventTime() {
        //Wochentag ermittel und den entsprechende Zeit in time Event schreiben
        $wochentage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
        $day =  $wochentage[date("w")];   
        //falls Sa oder So dann Werte aus SaSo Zeit schreiben
        if($day === "Samstag" || $day === "Sonntag"){
            $SZ_SaSo = getvalue($this->GetIDForIdent("SZ_SaSo"));
            $UpTimeSaSo = substr($SZ_SaSo,0,5);
            $UpSaSo_H = date("H", strtotime($UpTimeSaSo)); 
            $UpSaSo_M = date("i", strtotime($UpTimeSaSo));
            $DownTimeSaSo = substr($SZ_SaSo,8,5);
            $DownSaSo_H = date("H", strtotime($DownTimeSaSo)); 
            $DownSaSo_M = date("i", strtotime($DownTimeSaSo));
            $UpEventID = $this->GetIDForIdent("Up".$this->InstanceID);
            IPS_SetEventCyclicTimeFrom($UpEventID, $UpSaSo_H, $UpSaSo_M, 0);
            IPS_SetEventScript($UpEventID, "FSS_checkAutMode(\$_IPS['TARGET'], 'up');");  
            $DownEventID = $this->GetIDForIdent("Down".$this->InstanceID);
            IPS_SetEventCyclicTimeFrom($DownEventID, $DownSaSo_H, $DownSaSo_M, 0);
            IPS_SetEventScript($DownEventID, "FSS_checkAutMode(\$_IPS['TARGET'], 'down');");  
        }
        else {
            $SZ_MoFr = getvalue($this->GetIDForIdent("SZ_MoFr"));
            $UpTimeMoFr = substr($SZ_MoFr,0,5);
            $UpMoFr_H = date("H", strtotime($UpTimeMoFr)); 
            $UpMoFr_M = date("i", strtotime($UpTimeMoFr));
            $DownTimeMoFr = substr($SZ_MoFr,8,5);
            $DownMoF_H = date("H", strtotime($DownTimeMoFr)); 
            $DownoFr_M = date("i", strtotime($DownTimeMoFr));
            $UpEventID = $this->GetIDForIdent("Up".$this->InstanceID);
            IPS_SetEventCyclicTimeFrom($UpEventID, $UpMoFr_H, $UpMoFr_M, 0);
            IPS_SetEventScript($UpEventID, "FSS_checkAutMode(\$_IPS['TARGET'],'up');");  
            $DownEventID = $this->GetIDForIdent("Down".$this->InstanceID);
            IPS_SetEventCyclicTimeFrom($DownEventID, $DownMoF_H, $DownoFr_M, 0);
            IPS_SetEventScript($DownEventID, "FSS_checkAutMode(\$_IPS['TARGET'], 'down');");  
        }

    } 
            
    //*****************************************************************************
    /* Function: checkAutMode
    ...............................................................................
     * fährt den Rolladen auf 0% = Auf = Up bzw Down - zu wenn Kriterien erfüllt sind
     * Auslöser ist eine Zeit Trigger
     *
    ...............................................................................
    Parameters: 
       $direction -> up / down
    --------------------------------------------------------------------------------
    Returns:    
        none
    //////////////////////////////////////////////////////////////////////////////*/
    public function checkAutMode(string $direction) {
        //prüfen ob über ein Event gesteuert wird
       //$_IPS['EVENT']
        if ($_IPS["SENDER"] === "TimerEvent"){
            //$this->MyLog("checkAutMode", "Timer Event wurde erkannt.", true, true);
            //falls Auto - Mode dann ausführen
            $mode = getvalue($this->GetIDForIdent("Mode"));
            if (true) {
                //$this->MyLog("checkAutMode", "Rolladen steht auf Automatik.", true, true);
                if($direction === "down"){
                    //prüfen ob Türkontakt vorhanden und Tür zu
                    if($this->ReadPropertyInteger('Door_ID') > 0  && getvalue($this->ReadPropertyInteger('Door_ID')) === false){
                        //$this->MyLog("checkAutMode", "Fahre Rolladen Runter.", true, true);
                        $this->SetRolloDown();
                    }
                    //kein Türkontakt vorhanden
                    elseif($this->ReadPropertyInteger('Door_ID') === 0){
                        $this->SetRolloDown();
                        //$this->MyLog("checkAutMode", "Kein Türkontakt vorhanden.", true, true);
                    }
                    else{
                        //$this->MyLog("checkAutMode", "Türoffen. Rolladen wird nicht gefahren.", true, true);
                    }
                }
                elseif ("up"){
                    //$this->MyLog("checkAutMode", "Fahre Rolladen Hoch.", true, true);
                    $this->SetRolloUp();
                }
                else {
                    //falscher Parameter
                }
            }
            else{
               // Man Mode keine Aktion 
            }
        }
        else {   
            // kein Event Signal
        }
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
        if($this->ReadPropertyBoolean("negate")){
            //$this->SendDebug( "SetRolloDown", "Fahre Rolladen runter", 0); 
            $Tdown = $this->ReadPropertyFloat('Time_OU'); 
            FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, $Tdown); 
            Setvalue($this->GetIDForIdent("UpDown"),true); 
            SetValue($this->GetIDForIdent("FSSC_Timer"),time());
            $this->SetTimerInterval("LaufzeitTimer", 35000);
            $this->updateSwitchTimes();  // vorgabe Zeit schreiben
            $this->SetEventTime();  // neue Eventzeit setzten
        }else{
            //$this->SendDebug( "SetRolloUp", "Fahre Rolladen hoch", 0); 
            $Tup = $this->ReadPropertyFloat('Time_UO'); 
            FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, $Tup); 
            Setvalue($this->GetIDForIdent("UpDown"),false);
            SetValue($this->GetIDForIdent("FSSC_Timer"),time());
            $this->SetTimerInterval("LaufzeitTimer", 35000);
            $this->updateSwitchTimes(); 
            $this->SetEventTime();   
        }
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
        if($this->ReadPropertyBoolean("negate")){
            //$this->SendDebug( "SetRolloUp", "Fahre Rolladen hoch", 0); 
            $Tup = $this->ReadPropertyFloat('Time_UO'); 
            FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), true, $Tup); 
            Setvalue($this->GetIDForIdent("UpDown"),false);
            SetValue($this->GetIDForIdent("FSSC_Timer"),time());
            $this->SetTimerInterval("LaufzeitTimer", 35000);
            $this->updateSwitchTimes(); 
            $this->SetEventTime(); 
        }else{
            //$this->SendDebug( "SetRolloDown", "Fahre Rolladen runter", 0); 
            $Tdown = $this->ReadPropertyFloat('Time_OU'); 
            FS20_SwitchDuration($this->ReadPropertyInteger("FS20RSU_ID"), false, $Tdown); 
            Setvalue($this->GetIDForIdent("UpDown"),true); 
            SetValue($this->GetIDForIdent("FSSC_Timer"),time());
            $this->SetTimerInterval("LaufzeitTimer", 35000);
            $this->updateSwitchTimes();  // vorgabe Zeit schreiben
            $this->SetEventTime();  // neue Eventzeit setzten
        }
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
    public function SetRollo(int $pos) {
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
     Function: updateSwitchTimes
    ...............................................................................
    
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------ */
    protected function updateSwitchTimes(){
        $sunrise = getvalue($this->ReadPropertyInteger("SunRise_ID"));
        $sunset = getvalue($this->ReadPropertyInteger("SunSet_ID"));
        $OffSetSR_MoFr = getvalue($this->GetIDForIdent("OffSetSR_MoFr")) ;
        $OffSetSS_MoFr = getvalue($this->GetIDForIdent("OffSetSS_MoFr")) ;
        $OffSetSR_SaSo = getvalue($this->GetIDForIdent("OffSetSR_SaSo"));
        $OffSetSS_SaSo = getvalue($this->GetIDForIdent("OffSetSS_SaSo"));
        
        $sunriseA = date('H:i', $sunrise);
        $sunsetA = date('H:i', $sunset);
        
        $UpTime = getvalue($this->GetIDForIdent("SZ_MoFr"));
        $DownTime = getvalue($this->GetIDForIdent("SZ_SaSo"));
        
        //falls timer leer dann mit vorgabe füllen
        if($UpTime === ""){
            $UpTimeMoFr = date('H:i', $sunrise);
            $UpTimeSaSo = date('H:i', $sunrise);
        }
        if($DownTime === ""){
            $DownTimeMoFr = date('H:i', $sunset);
            $DownTimeSaSo = date('H:i', $sunset);
        }
        
        // falls SunSet aktiv dann nächste SunSet SunRise Werte mit Offset eintragen
        if($this->ReadPropertyBoolean('SunSet')){
            $UpTimeMoFr = date('H:i', strtotime($sunriseA) + $OffSetSR_MoFr *60);  
            $DownTimeMoFr = date('H:i',  strtotime($sunsetA) + $OffSetSS_MoFr *60);   
            $UpTimeSaSo = date('H:i', strtotime($sunriseA) + $OffSetSR_SaSo *60);  
            $DownTimeSaSo = date('H:i',  strtotime($sunsetA) + $OffSetSS_SaSo *60); 
        }
        
        
        // falls Wochenplan aktiv dann nächste Werte aus Wochenplan eintragen
        else{
            
        }
        
        
        setvalue($this->GetIDForIdent("SZ_MoFr"), $UpTimeMoFr." - ".$DownTimeMoFr);
        setvalue($this->GetIDForIdent("SZ_SaSo"), $UpTimeSaSo." - ".$DownTimeSaSo);
        
         
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
    protected function RegisterEvent(string $Name, string $Ident, int $Typ, int $Parent, int $Position)
    {
            $EventID = @$this->GetIDForIdent($Ident);
            if($EventID === false) {
                    $EventID = 0;
            } elseif(IPS_GetEvent($EventID)['EventType'] <> $Typ) {
                    IPS_DeleteEvent($EventID);
                    $EventID = 0;
            }
            //we need to create one
            if ($EventID == 0) {
                    $EventID = IPS_CreateEvent($Typ);
                    IPS_SetParent($EventID, $Parent);
                    IPS_SetIdent($EventID, $Ident);
                    IPS_SetName($EventID, $Name);
                    IPS_SetPosition($EventID, $Position);
                    IPS_SetEventActive($EventID, false);  
            }
            return $EventID;
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
    protected function RegisterScheduleAction($EventID, $ActionID, $Name, $Color, $Script)
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
    protected function createProfile(string $Name, int $Vartype, $Assoc, $Icon,  $Prefix,  $Suffix,   $MinValue,   $MaxValue,  $StepSize,  $Digits){
            if (!IPS_VariableProfileExists($Name)) {
                IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
                if(!is_Null($Icon)){
                    IPS_SetVariableProfileIcon($Name, $Icon);
                }
                if(!is_Null($Prefix)){
                    IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
                }
                if(!is_Null($Digits)){
                    IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
                }
                if(!is_Null($MinValue)){
                    IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
                }
                if(!is_Null($Assoc)){
                    foreach ($Assoc as $key => $data) {
                        if(is_null($data['icon'])){$data['icon'] = "";}; 
                        if(is_null($data['color'])){$data['color'] = "";}; 
                        IPS_SetVariableProfileAssociation($Name, $key, $data['value'], $data['icon'], $data['color']);  
                    }
                }
            } 
            else {
                $profile = IPS_GetVariableProfile($Name);
                if ($profile['ProfileType'] != $Vartype){
                       // $this->SendDebug("Alarm.Reset:", "Variable profile type does not match for profile " . $Name, 0);
                }
             }
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
    protected function GetIPSVersion()
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
 
    
    /* ----------------------------------------------------------------------------
     Function: RegisterProperties()
    ...............................................................................
        Variable aus dem Instanz Formular registrieren (zugänglich zu machen)
        Aufruf dieser Form Variable mit $Tup = $this->ReadPropertyFloat('IDENTNAME');
    ...............................................................................
    Parameters: 
        none
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    protected function RegisterProperties(){
        $this->RegisterPropertyBoolean("aktiv", false);
        $this->RegisterPropertyInteger("FS20RSU_ID", 0);
        $this->RegisterPropertyInteger ("SunSet_ID", 57942);
        $this->RegisterPropertyInteger ("SunRise_ID", 11938);
        $this->RegisterPropertyFloat("Time_UO", 0.5);
        $this->RegisterPropertyFloat("Time_OU", 0.5);
        $this->RegisterPropertyFloat("Time_UM", 0.5);
        $this->RegisterPropertyFloat("Time_OM", 0.5);
        $this->RegisterPropertyInteger("Door_ID", 0);
        $this->RegisterPropertyBoolean("SunSet", true);
        $this->RegisterPropertyBoolean("negate", false);
    }
    
    
    /* ----------------------------------------------------------------------------
     Function: Registerrofiles()
    ...............................................................................
        Profile fürVaiable anlegen falls nicht schon vorhanden
    ...............................................................................
    Parameters: 
        $Vartype => 0 boolean, 1 int, 2 float, 3 string
    ..............................................................................
    Returns:   
        $ipsversion
    ------------------------------------------------------------------------------- */
    protected function RegisterProfiles(){
            
        $Assoc[0]['value'] = "Manual";
        $Assoc[1]['value'] = "Automatic";
        $Name = "Rollo.Mode";
        $Vartype = 0;
        $Icon = NULL;
        $Prefix = NULL;
        $Suffix = NULL;
        $MinValue = NULL;
        $MaxValue = NULL;
        $StepSize = NULL;
        $Digits = NULL;
        $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);
                
        $Assoc[0] = "Up";
        $Assoc[1] = "Up";
        $Name = "Rollo.UpDown";
        $Vartype = 0;
        $Icon = NULL;
        $Prefix = NULL;
        $Suffix = NULL;
        $MinValue = NULL;
        $MaxValue = NULL;
        $StepSize = NULL;
        $Digits = NULL;
        $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);            
            
        $Assoc[0] = "off";
        $Assoc[1] = "on";
        $Name = "Rollo.SunSet";
        $Vartype = 0;
        $Icon = NULL;
        $Prefix = NULL;
        $Suffix = NULL;
        $MinValue = NULL;
        $MaxValue = NULL;
        $StepSize = NULL;
        $Digits = NULL;
        $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);       
            
        $Assoc = NULL;
        $Name = "Rollo.Position";
        $Vartype = 1;
        $Icon = 'Jalousie';
        $Prefix = NULL;
        $Suffix = ' %';
        $MinValue = 0;
        $MaxValue = 100;
        $StepSize = 1;
        $Digits = 0;
        $this->createProfile($Name, $Vartype,  $Assoc, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);  
            
                   
    }
    
    protected function MyLog($Titel, $data, bool $LogFile, bool $Debug) {
        $Directory=""; 
        $File="";
	if($LogFile){
            if ($File == ""){
                $File = 'IPSLog.log';
            }
            if ($Directory == "") {
                $Directory = "/home/pi/pi-share/";
            }
            if(($FileHandle = fopen($Directory.$File, "a")) === false) {
                Exit;
            }
            if (is_array($data)){
                //$comma_seperated=implode("\r\n",$array);
                $comma_seperated=print_r($data, true);
            }
            else {
                $comma_seperated = $data;
            }

            fwrite($FileHandle, $Text.": ");
            fwrite($FileHandle, $comma_seperated."\r\n");
            fclose($FileHandle);
        }
        if(Debug){
            if (is_object($data)) {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
                }
            } elseif (is_array($data)) {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
                }
            } else {
                if (is_bool($data)) {
                    parent::SendDebug($Message, ($data ? 'true' : 'false'), 0);
                } else {
                    parent::SendDebug($Message, (string) $data, $Format);
                }
            }
        }
    }       
        
    
    
}