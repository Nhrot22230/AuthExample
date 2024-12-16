variable "aws_region" {
  type    = string
  default = "us-east-1"
}

variable "project_name" {
  type    = string
  default = "mi-laravel-app"
}

variable "environment" {
  type    = string
  default = "prod"
}

variable "db_username" {
  type    = string
  default = "admin"
}

variable "db_password" {
  type    = string
  sensitive = true
  // Dejar vacío y setear via TF_VAR_db_password en GitHub Actions o Secrets
  default = ""
}

variable "db_name" {
  type    = string
  default = "app_db"
}

variable "db_instance_class" {
  type    = string
  default = "db.t3.micro"
}
resource "aws_s3_bucket" "app_bucket" {
  bucket = "${var.project_name}-${var.environment}-bucket"
  acl    = "private"
  versioning {
    enabled = true
  }
}

resource "aws_s3_bucket_public_access_block" "block_public_access" {
  bucket = aws_s3_bucket.app_bucket.id
  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}
