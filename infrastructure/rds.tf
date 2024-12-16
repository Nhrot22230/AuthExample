resource "aws_db_subnet_group" "default" {
  name       = "${var.project_name}-subnet-group"
  subnet_ids = data.aws_subnets.available.ids

  depends_on = [
    data.aws_subnets.available
  ]
}

data "aws_subnets" "available" {
  filter {
    name   = "default-for-az"
    values = ["true"]
  }
}

resource "aws_db_instance" "app_db" {
  allocated_storage    = 20
  storage_type         = "gp2"
  engine               = "mysql"
  instance_class       = var.db_instance_class
  name                 = var.db_name
  username             = var.db_username
  password             = var.db_password
  parameter_group_name = "default.mysql8.0"
  
  publicly_accessible = true
  skip_final_snapshot = true
  db_subnet_group_name = aws_db_subnet_group.default.name
}
