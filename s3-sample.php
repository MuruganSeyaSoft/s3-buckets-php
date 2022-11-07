<?php 

require 'vendor/autoload.php';

use Aws\S3\S3Client;


$region = 'us-west-2';
$version = 'latest';

$s3client = new S3Client([
    'region' => $region,
    'version' => $version
]);
/* Inline declaration example
$s3client = new Aws\S3\S3Client(['region' => 'us-west-2', 'version' => 'latest']);
*/

$bucket_name = "doc-example-bucket-" . uniqid();

try {
    $s3client->createBucket([
        'Bucket' => $bucket_name,
        'CreateBucketConfiguration' => ['LocationConstraint' => $region],
    ]);
    echo "Created bucket named: $bucket_name \n";
} catch (Exception $exception) {
    echo "Failed to create bucket $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with bucket creation before continuing.");
}

$file_name = "local-file-" . uniqid();
try {
    $s3client->putObject([
        'Bucket' => $bucket_name,
        'Key' => $file_name,
        'SourceFile' => 'testfile.txt'
    ]);
    echo "Uploaded $file_name to $bucket_name.\n";
} catch (Exception $exception) {
    echo "Failed to upload $file_name with error: " . $exception->getMessage();
    exit("Please fix error with file upload before continuing.");
}

try {
    $file = $s3client->getObject([
        'Bucket' => $bucket_name,
        'Key' => $file_name,
    ]);
    $body = $file->get('Body');
    $body->rewind();
    echo "Downloaded the file and it begins with: {$body->read(26)}.\n";
} catch (Exception $exception) {
    echo "Failed to download $file_name from $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with file downloading before continuing.");
}

try {
    $folder = "copied-folder";
    $s3client->copyObject([
        'Bucket' => $bucket_name,
        'CopySource' => "$bucket_name/$file_name",
        'Key' => "$folder/$file_name-copy",
    ]);
    echo "Copied $file_name to $folder/$file_name-copy.\n";
} catch (Exception $exception) {
    echo "Failed to copy $file_name with error: " . $exception->getMessage();
    exit("Please fix error with object copying before continuing.");
}

try {
    $contents = $s3client->listObjects([
        'Bucket' => $bucket_name,
    ]);
    echo "The contents of your bucket are: \n";
    foreach ($contents['Contents'] as $content) {
        echo $content['Key'] . "\n";
    }
} catch (Exception $exception) {
    echo "Failed to list objects in $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with listing objects before continuing.");
}

try {
    $objects = [];
    foreach ($contents['Contents'] as $content) {
        $objects[] = [
            'Key' => $content['Key'],
        ];
    }
    $s3client->deleteObjects([
        'Bucket' => $bucket_name,
        'Key' => $file_name,
        'Delete' => [
            'Objects' => $objects,
        ],
    ]);
    $check = $s3client->listObjects([
        'Bucket' => $bucket_name,
    ]);
    if (count($check) <= 0) {
        throw new Exception("Bucket wasn't empty.");
    }
    echo "Deleted all objects and folders from $bucket_name.\n";
} catch (Exception $exception) {
    echo "Failed to delete $file_name from $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with object deletion before continuing.");
}

try {
    $s3client->deleteBucket([
        'Bucket' => $bucket_name,
    ]);
    echo "Deleted bucket $bucket_name.\n";
} catch (Exception $exception) {
    echo "Failed to delete $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with bucket deletion before continuing.");
}

echo "Successfully ran the Amazon S3 with PHP demo.\n";


?>