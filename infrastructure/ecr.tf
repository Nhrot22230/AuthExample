resource "aws_ecr_repository" "app_repo" {
  name                 = "${var.project_name}-repo"
  image_tag_mutability = "MUTABLE"
  lifecycle_policy     = <<EOF
{
  "rules": [
    {
      "rulePriority": 1,
      "description": "Keep last 10 images",
      "selection": {
        "tagStatus": "any",
        "countType": "imageCountMoreThan",
        "countNumber": 10
      },
      "action": {
        "type": "expire"
      }
    }
  ]
}
EOF
}
