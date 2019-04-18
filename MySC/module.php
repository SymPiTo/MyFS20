<?php

//require_once(__DIR__ . "/../libs/NetworkTraits1.php");

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
class MyFS20_SC extends IPSModule
{
    //externe Klasse einbinden - ueberlagern mit TRAIT.
    //use MyDebugHelper1;
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
        $this->RegisterPropertyInteger("FS20RSU_ID", 0);
        $this->RegisterPropertyInteger ("SunSet_ID", 57942);
        $this->RegisterPropertyInteger ("SunRise_ID", 11938);
        $this->RegisterPropertyFloat("Time_OU", 0.5);
        $this->RegisterPropertyFloat("Time_UO", 0.5);
        $this->RegisterPropertyFloat("Time_OM", 0.5);
        $this->RegisterPropertyFloat("Time_UM", 0.5);
        $this->RegisterPropertyBoolean("SunRiseActive", false);
        
 
        
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


    }

    /* ---------------------------------------------------------------------------
     Function: SetSwitchPoint
    ...............................................................................
    setzt einen Schaltpunkt.
    ...............................................................................
    Parameters: 
        $switchGroup    -   0 = Mo-Fr // 1 = Sa-So
        $switchPoint    -   0 = Up // 1 = Down
        $h              -   time - hour
        $m              -   time - minute
        $action         -   0 = Up  // 1 = Down
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------ */
    public function SetSwitchPoint(int $switchGroup, int $switchPoint, int $h, int $m, int $action) {
 
        
    }    
    
    /* ---------------------------------------------------------------------------
     Function: SetSunSet
    ...............................................................................
    setzt  Schaltpunkte auf SunSet und SunRise.
    ...............................................................................
    Parameters: 
 
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------ */
    public function SetSunSet(bool $value){

    
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
    private function updateSunRise(){

    }    
        

    
    /* ---------------------------------------------------------------------------
     Function: GetWochenplanAction
    ...............................................................................
    
    ...............................................................................
    Parameters: 
        none
    ...............................................................................
    Returns:    
        none
    ------------------------------------------------------------------------------ */
    public function GetWochenplanAction() 
    { 

        
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