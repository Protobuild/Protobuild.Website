using Newtonsoft.Json;

namespace Protobuild.Website.Models
{
    public class VersionModel
    {
        public const string Kind = "version";

        [JsonProperty("key")]
        public long Key { get; set; }

        [JsonProperty("googleId")]
        public string GoogleId { get; set; }

        [JsonProperty("packageName")]
        public string PackageName { get; set; }

        [JsonProperty("platformName")]
        public string PlatformName { get; set; }

        [JsonProperty("versionName")]
        public string VersionName { get; set; }

        [JsonProperty("archiveType")]
        public string ArchiveType { get; set; }

        [JsonProperty("hasFile")]
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

        public static VersionModel FromJsonCache(string jsonString)
        {
            return JsonConvert.DeserializeObject<VersionModel>(jsonString);
        }

        public string ToJsonCache()
        {
            return JsonConvert.SerializeObject(this);
        }
    }
}
