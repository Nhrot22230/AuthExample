terraform {
  required_version = ">= 1.0.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  backend "s3" {
    bucket = "mi-bucket-de-estado-terraform"
    key    = "estado.tfstate"
    region = "us-east-1"
  }

}

provider "aws" {
  region = var.aws_region
}
