# IP-Camera recording system

Camera recording management system that uses VLC media player to record from network video devices.

__Features:__

* Camera configuration stored in DB
* One VLC process for each device
* Monitors and restarts VLC processes upon failure
* Logs activity such as device recording start/stop and camera disconnected
* Ability to use different recording format for every device
* Can record to the same or different locations for each device
* Cron scripts to remove old recordings when storage limit reached

__Requirements:__

* PHP enabled web server (Apache)
* MySQL or MariaDB database
* VLC binaries
* Linux based OS (does not work correctly on Windows. Only tested on Debian GNU/Linux but should work fine on other *nix like OS)


__Setup:__

1. Copy files to web server webroot
2. Create folders for recordings, logs, PID's and locks. Make sure they are writable to the web server
3. Insert "ipcam_db.sql" then edit "include/db.php" to add database configuration (address, username, password etc...)
4. Log in using username and password "admin"
5. Browse to __*ADMIN SETTINGS/CONFIGURATION MANAGER*__ and add in necessary configuration:
 * __vlc_exec__ - VLC executable. Can be a full path or just a command (if it is in your $PATH)
 * __recording\_directory__ - Path to recordings directory
 * __pid\_directory__ - Path to PID directory. Used to store PID files of running VLC instances
 * __log\_directory__ - Path to log directory. Used to store VLC instance logs
 * __lock\_directory__ - Path to lock directory. Used by cron job lock files
 * __recording\_file\_extension__ - Filename extension for recording files
 * __recording\_file\_mux__ - Set muxer for recordings
 * __transcode\_enable__ - Enable video transcoding. This will require a fast CPU
 * __recording\_device\_limit__ - Percentage of disk space that must be is use before old recordings are removed (via cron)
6. Add device settings via the __*EDIT CAMERAS*__ page accessible from the main page
7. Click __*START ALL*__ to begin recording from all devices

__Cron Files__

Cron scripts to delete old or failed recordings are available in the __cron__ folder.


