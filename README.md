Simplified Moodle for development [fork](https://github.com/LeonardJouve/moodle-docker) on docker

## Deployment

Create EC2 t3.medium with terraform or if you already own a machine with SSH access skip this step

```bash
cd terraform
terraform init
terraform apply
```

Configure the instance using Ansible

First modify `ansible/inventory.ini` with your SSH credentials

Then modify `ansible/group_vars/all.yaml` with your host and url

Finally run Ansible playbooks

```
cd ansible
ansible-playbook -i inventory.ini playbooks/base.yaml
ansible-playbook -i inventory.ini playbooks/traefik.yaml
ansible-playbook -i inventory.ini playbooks/authentik.yaml
ansible-playbook -i inventory.ini playbooks/moodle.yaml
```

Setup authentik by browsing `http://<authentik_external_host>/if/flow/initial-setup/`

Your app should now be running and accessible with `http://<moodle_external_host>`