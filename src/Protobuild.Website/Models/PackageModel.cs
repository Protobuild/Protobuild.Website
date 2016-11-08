using Newtonsoft.Json;

namespace Protobuild.Website.Models
{
    public class PackageModel
    {
        public const string Kind = "package";

        public const string TypeLibrary = "library";

        public const string TypeTemplate = "template";

        public const string TypeGlobalTool = "global-tool";

        [JsonProperty("key")]
        public long Key { get; set; }

        [JsonProperty("googleId")]
        public string GoogleId { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("gitUrl")]
        public string GitUrl { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("type")]
        public string Type { get; set; }

        [JsonProperty("defaultBranch")]
        public string DefaultBranch { get; set; }

        public string GetUrl(UserModel owner, string path = null)
        {
            if (string.IsNullOrWhiteSpace(path))
            {
                return owner.GetUrl(Name);
            }
            else
            {
                return owner.GetUrl(Name + "/" + path);
            }
        }

        public object ToJsonObject(UserModel owner)
        {
            return new
            {
                ownerID = GoogleId,
                name = Name,
                type = Type,
                moduleUrl = ProtobuildEnv.GetDomain() + GetUrl(owner),
                apiUrl = ProtobuildEnv.MakeApiUrl(ProtobuildEnv.GetDomain() + GetUrl(owner)),
                gitUrl = GitUrl,
                description = Description,
                defaultBranch = DefaultBranch
            };
        }

        public static PackageModel FromJsonCache(string jsonString)
        {
            return JsonConvert.DeserializeObject<PackageModel>(jsonString);
        }

        public string ToJsonCache()
        {
            return JsonConvert.SerializeObject(this);
        }
    }
}
