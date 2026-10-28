USE softeng2;

ALTER TABLE jobs ADD COLUMN eligible_education TEXT NOT NULL AFTER department;
UPDATE jobs SET department = 'Information Technology',
    eligible_education = 'Bachelor of Science in Computer Science|Bachelor of Science in Information Technology'
WHERE eligible_education = '';
