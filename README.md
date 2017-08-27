# This repository is archived

In years gone past, Protobuild provided it's own package index where platform-specific packages were served from. As time went on, the Protobuild client obtained the ability to store it's platform-specific packages on NuGet without any additional external infrastructure required, which made the Protobuild package index obsolete.

Eventually, the APIs that the original index used for storaging packages and metadata were deprecated, and this meant the index required a full rewrite.  Originally in PHP, a new version of the Protobuild index was written in C# with read-only functionality, to ensure that packages stored on the old index could still be resolved while the migration to NuGet took place one package at a time.

Since then, all maintained packages stored on the Protobuild index have since been migrated to NuGet where they now officially live. With this done, the Protobuild website could now be stored entirely as static files (as it no longer had any dynamic functionality) and as such, the Protobuild website is now served from a Google Cloud Storage bucket.

While you can possibly fork this repository and run your own package index that way, you should be aware that support for the original Protobuild package index protocol will most likely be removed in an upcoming release of the Protobuild client. 

This repository continues to exist for archival purposes.
