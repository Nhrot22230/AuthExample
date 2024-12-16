output "load_balancer_dns_name" {
  value = aws_lb.app_lb.dns_name
}

output "db_endpoint" {
  value = aws_db_instance.app_db.address
}

output "s3_bucket_name" {
  value = aws_s3_bucket.app_bucket.bucket
}

output "ecr_repository_url" {
  value = aws_ecr_repository.app_repo.repository_url
}
