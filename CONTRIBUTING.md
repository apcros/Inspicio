## Setup environement

- Download and install Vagrant (https://www.vagrantup.com/)
- Clone https://github.com/apcros/Inspicio-DevTools locally
- Create a mailtrap account https://mailtrap.io/
- Create a Dev Oauth  application for GitHub( https://github.com/settings/applications/new )
  
  Homepage and callback will be https://inspicio.devbox

- Create a Dev Oauth application for Bitbucket ( https://bitbucket.org/account/user/Apcros/oauth-consumers/new)

  Homepage will be https://inspicio.devbox and callback will be https://inspicio.devbox/oauth/callback/bitbucket/ )

Please note that you don't need both Bitbucket and Github, you need at least one. (It's recommended to setup both)

- Setup the following env variables : 

  MAIL_USERNAME=...Your mailtrap SMTP username...
  MAIL_PASSWORD=... Your mailtrap SMTP password..
  GITHUB_CLIENT_ID=..Your dev Github app oauth client_id..
  GITHUB_SECRET=...Your dev Github app oauth secret..
  BITBUCKET_CLIENT_ID=..Your dev Bitbucket app oauth client_id..
  BITBUCKET_SECRET=..Your dev Bitbucket app oauth secret..

- Cd in Inspicio-DevTools/vagrant-box
- Run vagrant up

The first provisioning will take several minutes please be patient.
Once done, you'll be able to access inspicio.devbox from your browser on your host.
All the Inspicio code will be in Inspicio-DevTools/vagrant-box/vm-www-data, You can edit from your host, or directly from the vm.

Once you're happy with your changes, run phpunit from the vm while being in the Inspicio directory.
Commit directly from the vm. (Will add support for SSH key to be sent to the vm in the near futur)
