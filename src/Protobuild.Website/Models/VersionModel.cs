namespace Protobuild.Website.Models
{
    public class VersionModel
    {
        public const string Kind = "version";

        public long Key { get; set; }

        public string GoogleId { get; set; }

        public string PackageName { get; set; }

        public string PlatformName { get; set; }

        public string VersionName { get; set; }

        public string ArchiveType { get; set; }

        public bool HasFile { get; set; }

        public object ToJsonObject()
        {
            return new
            {
                ownerID = GoogleId,
                packageName = PackageName,
                versionName = VersionName,
                platformName = PlatformName,
                hasFile = HasFile,
                archiveType = ArchiveType,
                downloadUrl = GetDownloadUrl()
            };
        }

        public string GetDownloadUrl()
        {
            const string prefix = "https://storage.googleapis.com/protobuild-packages/";
            return prefix + GetFilenameForStorage();
        }

        public string GetFilenameForStorage()
        {
            return Key + ".pkg";
        }
    }
}
