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
        $this->RegisterPropertyInteger("FS20RSU_ID", 0);
        $this->RegisterPropertyInteger ("SunSet_ID", 57942);
        $this->RegisterPropertyInteger ("SunRise_ID", 11938);
        $this->RegisterPropertyFloat("Time_UO", 0.5);
        $this->RegisterPropertyFloat("Time_OU", 0.5);
        $this->RegisterPropertyFloat("Time_UM", 0.5);
        $this->RegisterPropertyFloat("Time_OM", 0.5);
        $this->RegisterPropertyBoolean("SunRiseActive", false);
        
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
        
        // Profile den Variablen zuordnen   
        IPS_SetVariableCustomProfile($this->GetIDForIdent("FSSC_Position"), "Rollo.Position");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("UpDown"), "Rollo.UpDown");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("Mode"), "Rollo.Mode");
        IPS_SetVariableCustomProfile($this->GetIDForIdent("SS"), "Rollo.SunSet"); 

     
        // Aktiviert die Standardaktion der Statusvariable zur Bedienbarkeit im Webfront
        $this->EnableAction("FSSC_Position");
        $this->EnableAction("UpDown");
        $this->EnableAction("Mode");
        $this->EnableAction("SS");
        
        //anlegen eines Timers
        $this->RegisterTimer("LaufzeitTimer", 0, "FSSC_reset(\$_IPS['TARGET']);");
        
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
         switch($Ident) {
            case "FSSC_Position":
                //Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
                //Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet
                //$this->setRollo($Value);

                //Neuen Wert in die Statusvariable schreiben
                //SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case "UpDown":
                //SetValue($this->GetIDForIdent($Ident), $Value);
                if(getvalue($this->GetIDForIdent($Ident))){
                    //$this->SetRolloDown();  
                }
                else{
                   // $this->SetRolloUp();
                }
                break;
             case "Mode":
               // $this->SetMode($Value);  
                break;
             case "SS":
               // $this->SetSunSet($Value);  
                break;
            default:
                throw new Exception("Invalid Ident");
        }
    }
    /*  ----------------------------------------------------------------------------------------------------------------- 
     Section: Public Funtions
     Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wie folgt zur Verfügung gestellt:
    
     FSSC_XYFunktion($Instance_id, ... );
     ---------------------------------------------------------------------------------------------------------------------  */


  
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}