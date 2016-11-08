using Newtonsoft.Json;

namespace Protobuild.Website.Models
{
    public class UserModel
    {
        public const string Kind = "user";

        [JsonProperty("key")]
        public long Key { get; set; }

        [JsonProperty("googleId")]
        public string GoogleId { get; set; }

        [JsonProperty("uniqueName")]
        public string UniqueName { get; set; }

        [JsonProperty("canonicalName")]
        public string CanonicalName { get; set; }

        [JsonProperty("isOrganisation")]
        public bool IsOrganisation { get; set; }

        [JsonProperty("apiKey")]
        public string ApiKey { get; set; }
        
        [JsonIgnore]
        public string Term
        {
            get
            {
                if (IsOrganisation)
                {
                    return "organisation";
                }
                else
                {
                    return "user";
                }
            }
        }

        public object ToJsonObject()
        {
            return new
            {
                id = GoogleId,
                canonicalName = CanonicalName,
                uniqueName = UniqueName,
                isOrganisation = IsOrganisation,
                term = Term,
                url = ProtobuildEnv.GetDomain() + GetUrl(),
                apiUrl = ProtobuildEnv.MakeApiUrl(ProtobuildEnv.GetDomain() + GetUrl())
            };
        }

        public string GetUrl(string path = null)
        {
            if (string.IsNullOrWhiteSpace(path))
            {
                return "/" + CanonicalName;
            }
            else
            {
                return "/" + CanonicalName + "/" + path;
            }
        }

        public static UserModel FromJsonCache(string jsonString)
        {
            return JsonConvert.DeserializeObject<UserModel>(jsonString);
        }

        public string ToJsonCache()
        {
            return JsonConvert.SerializeObject(this);
        }
    }
}
