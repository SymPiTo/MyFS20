
#<?php

// require_once(__DIR__ . "/../libs/NetworkTraits3.php");

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
	
        $this->RegisterProperties();
      

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
    
    

    
}