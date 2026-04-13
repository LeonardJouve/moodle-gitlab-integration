provider "aws" {
    region = "eu-central-1"
}

data "aws_ami" "ubuntu" {
    most_recent = true

    owners = ["099720109477"] # Canonical

    filter {
        name   = "name"
        values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*"]
    }
}

resource "tls_private_key" "ssh_key" {
    algorithm = "RSA"
    rsa_bits  = 4096
}

resource "local_file" "private_key" {
    content  = tls_private_key.ssh_key.private_key_pem
    filename = "${path.module}/key.pem"
}

resource "aws_key_pair" "generated" {
    key_name   = "moodle-gitlab-integration-ssh-key"
    public_key = tls_private_key.ssh_key.public_key_openssh
}

resource "aws_instance" "dev" {
    ami           = data.aws_ami.ubuntu.id
    instance_type = "t3.micro"
    key_name      = aws_key_pair.generated.key_name
    vpc_security_group_ids = [aws_security_group.dev_sg.id]
    
    tags = {
        Name = "moodle-gitlab-integration-ec2"
    }
}

resource "aws_security_group" "dev_sg" {
    name = "moodle-gitlab-integration-sg"

    ingress {
        from_port   = 22
        to_port     = 22
        protocol    = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port   = 80
        to_port     = 80
        protocol    = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port   = 443
        to_port     = 443
        protocol    = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

resource "aws_eip" "dev_eip" {
    instance = aws_instance.dev.id
    domain   = "vpc"
}