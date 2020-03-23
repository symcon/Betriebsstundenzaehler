# Betriebsstundenzähler
Beschreibung des Moduls.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Anzeige der Stunden, welche ein Gerät aktiv ist.
* Hinzufügen des Geräts furch eine Boolsche-Variable.

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.2

### 3. Software-Installation

* Über den Module Store das 'Betriebsstundenzähler'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' ist das 'Betriebsstundenzähler'-Modul unter dem Hersteller '(Gerät)' aufgeführt.

__Konfigurationsseite__:

Name                     | Beschreibung
------------------------ | ------------------
Quelle                   | Die Variable, deren Betriebszeit angezeigt werden soll.
Stufe                    | Die Länge des Zeitraumes, welcher beobachtet wird (Tag , Woche, Monat, Jahr, Gesamt).
Aktualisierungsintervall | Der Intervall in dem der Wert aktualisiert wird.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name            | Typ   | Beschreibung
--------------- | ----- | ------------
Betriebsstunden | float | Die Anzahl der Stunden, welche die Quellvariable aktiv war.

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