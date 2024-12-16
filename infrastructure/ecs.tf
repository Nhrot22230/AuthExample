# ECS Cluster
resource "aws_ecs_cluster" "app_cluster" {
  name = "${var.project_name}-cluster"
}

# Obtenemos la VPC por defecto
data "aws_vpc" "default" {
  default = true
}

# Subnets por defecto (asume que la VPC default tiene subnets marcadas como "default-for-az")
data "aws_subnets" "default_subnets" {
  filter {
    name   = "default-for-az"
    values = ["true"]
  }
}

# Security Group para el servicio ECS
resource "aws_security_group" "ecs_sg" {
  name        = "${var.project_name}-ecs-sg"
  description = "Security group for ECS service"
  vpc_id      = data.aws_vpc.default.id

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"] # Ajustar para mayor seguridad
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# Load Balancer
resource "aws_lb" "app_lb" {
  name               = "${var.project_name}-lb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.ecs_sg.id]
  subnets            = data.aws_subnets.default_subnets.ids
}

resource "aws_lb_target_group" "app_tg" {
  name     = "${var.project_name}-tg"
  port     = 80
  protocol = "HTTP"
  vpc_id   = data.aws_vpc.default.id
  target_type = "ip"
}

resource "aws_lb_listener" "app_listener" {
  load_balancer_arn = aws_lb.app_lb.arn
  port              = 80
  protocol          = "HTTP"

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.app_tg.arn
  }
}

# Task Definition para ECS (usará el rol creado en iam.tf)
resource "aws_ecs_task_definition" "app_task" {
  family                   = "${var.project_name}-task"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512

  container_definitions = jsonencode([
    {
      name      = "app"
      image     = aws_ecr_repository.app_repo.repository_url
      essential = true
      portMappings = [
        {
          containerPort = 80
          hostPort      = 80
        }
      ]
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          "awslogs-group"         = "/ecs/mi-laravel-app"
          "awslogs-region"        = "us-east-1" # Cambia según tu región
          "awslogs-stream-prefix" = "ecs"
        }
      }
      environment = [
        {
          name  = "DB_HOST"
          value = aws_db_instance.app_db.address
        },
        {
          name  = "DB_DATABASE"
          value = var.db_name
        },
        {
          name  = "DB_USERNAME"
          value = var.db_username
        },
        {
          name  = "DB_PASSWORD"
          value = var.db_password
        },
        {
          name  = "S3_BUCKET"
          value = aws_s3_bucket.app_bucket.bucket
        },{
          name  = "APP_KEY"
          value = "base64:m2IOOijVL+ktI6Pwh5iTcObwoOvi+A0He0YknnO6GGY=="
        },{
          name = "SEEDERS"
          value = "true"
        }
      ]
    }
  ])

  # Estos roles se crean en iam.tf
  execution_role_arn = aws_iam_role.ecs_task_execution_role.arn
  task_role_arn      = aws_iam_role.ecs_task_execution_role.arn
}

# ECS Service
resource "aws_ecs_service" "app_service" {
  name            = "${var.project_name}-service"
  cluster         = aws_ecs_cluster.app_cluster.id
  task_definition = aws_ecs_task_definition.app_task.arn
  desired_count   = 1
  launch_type     = "FARGATE"
  network_configuration {
    subnets         = data.aws_subnets.default_subnets.ids
    security_groups = [aws_security_group.ecs_sg.id]
    assign_public_ip = true
  }
  load_balancer {
    target_group_arn = aws_lb_target_group.app_tg.arn
    container_name   = "app"
    container_port   = 80
  }

  depends_on = [aws_lb_listener.app_listener]

  force_new_deployment = true
}
