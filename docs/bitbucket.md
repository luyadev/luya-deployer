# Bitbucket

1. Login in to your server via ssh from Terminal. `ssh username@domain.tld`
2. Create an SSH key with read only access on your server. `ssh-keygen -f ~/.ssh/id_rsa_ro -t rsa -C "email@domain.tld"` 
3. And add the created key pair, `id_rsa_ro` and `id_rsa_ro.pub` to your BitBucket SSH Section in the  `Repo->Settings -> SSH`, **not** in the profile SSH key section.
4. Modify your ssh configuration on your server. Edit `vim ~/.ssh/config` or create a new file.

Add something like this to your configuration file:

```sh
Host bitbucket.org-ro
    HostName bitbucket.org
    IdentityFile ~/.ssh/id_rsa_ro
Host
```

make sure the that the correct permissions and owner are applied to the created config file:

```sh
chmod 600 ~/.ssh/config
chown $USER ~/.ssh/config
```

5. Adding BitBucket to `known_hosts` on your server.

```sh
vim ~/.ssh/known_hosts
```

Usually the RSA fingerpint is established automatically on approval if you run `git clone` via ssh, but here we need to add it manually.

```sh
bitbucket.org,104.192.143.2 [<your-secret id_rsa_ro.pub ssh key>]
```

6. Setup up your local `deploy.php` as decribed above and run the deployment `./vendor/bin/dep luya <stage>`.
