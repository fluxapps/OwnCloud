# OwnCloud Plugin for ILIAS
Allows the connection of an OwnCloud platform to ILIAS LMS through ILIAS' Cloud Objects. These are the main features:
* Browse, create/delete folders, upload/download/delete files directly from/to OwnCloud
* Choose a root folder
* *OAuth2* or *Basic Digest* Authentication
* Fast download, avoiding php time/memory limit, no temporary files

Have a look at the [full Documentation](/doc/Documentation.pdf?raw=true)

## Getting Started

### Requirements

* ILIAS 6.x / 7.x

### Installation

Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Modules/Cloud/CloudHook/
cd Customizing/global/plugins/Modules/Cloud/CloudHook/
git clone https://github.com/fluxapps/OwnCloud.git
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

## Rebuild & Maintenance

fluxlabs ag, support@fluxlabs.ch

This project needs a proof of concept

Would you like to participate?
Take advantage of the crowdfunding opportunity under [discussions](https://github.com/fluxapps/OwnCloud/discussions/4).


## About fluxlabs plugins

Please also have a look at our other key projects and their [MAINTENANCE](https://github.com/fluxapps/docs/blob/8ce4309b0ac64c039d29204c2d5b06723084c64b/assets/MAINTENANCE.png).

The plugins that require a rebuild and the costs are listed here: [REBUILDS](https://github.com/fluxapps/docs/blob/8ce4309b0ac64c039d29204c2d5b06723084c64b/assets/REBUILDS.png)
