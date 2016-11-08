using Newtonsoft.Json;

namespace Protobuild.Website.Models
{
    public class BranchModel
    {
        public const string Kind = "branch";

        [JsonProperty("key")]
        public long Key { get; set; }

        [JsonProperty("googleId")]
        public string GoogleId { get; set; }

        [JsonProperty("packageName")]
        public string PackageName { get; set; }

        [JsonProperty("branchName")]
        public string BranchName { get; set; }

        [JsonProperty("versionName")]
        public string VersionName { get; set; }

        [JsonProperty("isAutoBranch")]
        public bool IsAutoBranch { get; set; }

        public object ToJsonObject()
        {
            return new
            {
                ownerID = GoogleId,
                packageName = PackageName,
                branchName = BranchName,
                versionName = VersionName
            };
        }

        public static BranchModel FromJsonCache(string jsonString)
        {
            return JsonConvert.DeserializeObject<BranchModel>(jsonString);
        }

        public string ToJsonCache()
        {
            return JsonConvert.SerializeObject(this);
        }
    }
}
