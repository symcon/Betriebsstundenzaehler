# Betriebsstundenzähler
Mithilfe des Betriebsstundenzähler-Moduls kann die Betriebszeit eines Gerätes ermittelt und angezeigt werden.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Anzeige der Stunden, welche ein Gerät aktiv ist
* Hinzufügen des Geräts durch eine Variable vom Typ Boolean

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.2

### 3. Software-Installation

* Über den Module Store das 'Betriebsstundenzähler'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen: `https://github.com/symcon/Betriebsstundenzaehler`

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' ist das 'Betriebsstundenzähler'-Modul unter dem Hersteller '(Gerät)' aufgeführt.

__Konfigurationsseite__:

Name                     | Beschreibung
------------------------ | ------------------
Quelle                   | Die Variable vom Typ Boolean, deren Betriebszeit angezeigt werden soll.
Die Variable vom Typ Boolean, welche den Aktivitätsstatus eines Gerätes anzeigt. Um die Betriebsstunden zu errechnen muss diese Variable geloggt sein.  
Stufe                    | Die Stufe legt den Beginn des Zeitraums fest welcher betrachtet wird. (Beginn des Tages, Woche, Monat, Jahr)
Aktualisierungsintervall | Der Intervall in Minuten in dem die Betriebszeit erneut berechnet wird Betriebszeit wird.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name            | Typ   | Beschreibung
--------------- | ----- | ------------
Betriebsstunden | float | Die berechneten Betriebsstunden der Quellvariable im ausgewählten Zeitraum.

#### Profile

Name              | Typ
----------------- | -------
BSZ.OperatingHours| float

### 6. WebFront

Im Webfront werden die Betriebsstunden angezeigt.

### 7. PHP-Befehlsreferenz

`void BSZ_Calculate(integer $InstanzID);`

Die Betriebsstunden-Variable wird auf den errechneten Wert gesetzt.

Beispiel:
`BSZ_Calculate(12345);`