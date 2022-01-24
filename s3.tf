
terraform {
    required_providers {
	aws = {
	    source  = "hashicorp/aws"
	    version = "~> 3.27"
	}
    }	

    required_version = ">= 0.14.9"
}



provider "aws" {
    profile = "default"
    region  = "us-east-1"
}


resource "aws_s3_bucket" "example" {
  bucket = "example"
}

resource "aws_s3_bucket_public_access_block" "example" {
  bucket = aws_s3_bucket.example.id

  block_public_acls   = true
  block_public_policy = true
}
