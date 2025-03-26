CREATE TABLE "Users" (
  "user_id" INTEGER NOT NULL ,
  "system_role_id" INTEGER NOT NULL DEFAULT 2,
  "email" TEXT NOT NULL,
  "password_hash" TEXT NOT NULL,
  "first_name" TEXT NOT NULL,
  "last_name" TEXT NOT NULL,
  "created_at" timestamp NOT NULL,
  "last_login" timestamp NULL DEFAULT NULL,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  PRIMARY KEY("user_id" AUTOINCREMENT)
);

CREATE TABLE "organisations" (
  "org_id" INTEGER NOT NULL,
  "org_name" TEXT NOT NULL,
  "description" TEXT,
  "created_at" timestamp NOT NULL ,
  "updated_at" timestamp NOT NULL ,
  PRIMARY KEY("org_id" AUTOINCREMENT)
) ;

CREATE TABLE "organisation_members" (
  "org_member_id" INTEGER NOT NULL,
  "org_id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "role_id" INTEGER NOT NULL,
  "joined_at" timestamp NOT NULL,
  "invited_by" INTEGER DEFAULT NULL
) ;


CREATE TABLE "Project" (
	"project_id" INTEGER NOT NULL,
	"project_name" TEXT NOT NULL,
	"description" TEXT NOT NULL,
	"created_by" INTEGER NOT NULL,
	"latest_version" INTEGER NOT NULL,
	PRIMARY KEY("project_id")
);

CREATE TABLE "Project_File" (
	"project_file_id" INTEGER NOT NULL,
	"project_id" INTEGER NOT NULL,
	"file_name"	TEXT NOT NULL,
	"latest_version" INTEGER NOT NULL,
	"first_added_at_version" INTEGER NOT NULL,
	PRIMARY KEY("project_file_id" AUTOINCREMENT),
	FOREIGN KEY("project_id") REFERENCES "Project"("project_id")
);

CREATE TABLE "Project_Commit" (
	"commit_id" INTEGER NOT NULL,
	"commit_message" TEXT NOT NULL,
	"project_id" INTEGER NOT NULL,
	"project_version" INTEGER NOT NULL,
	PRIMARY KEY("commit_id" AUTOINCREMENT),
	FOREIGN KEY("project_id") REFERENCES "Project"("project_id")
);

CREATE TABLE "Bucket_File" (
	"bucket_file_id" INTEGER NOT NULL,
	"project_file_id" INTEGER NOT NULL,
	"file_version" INTEGER NOT NULL,
	"object_id" TEXT NOT NULL,
	"object_key" TEXT NOT NULL,
	"first_added_at_version" INTEGER NOT NULL,
	PRIMARY KEY("bucket_file_id" AUTOINCREMENT),
	FOREIGN KEY("project_file_id") REFERENCES "Project_File"("project_file_id")
);

CREATE TABLE "Commit_File" (
	"commit_id" INTEGER NOT NULL,
	"bucket_file_id" INTEGER NOT NULL,
	PRIMARY KEY("commit_id","bucket_file_id"),
	FOREIGN KEY("commit_id") REFERENCES "Project_Commit"("commit_id") ON DELETE CASCADE,
	FOREIGN KEY("bucket_file_id") REFERENCES "Bucket_File"("bucket_file_id")
);
