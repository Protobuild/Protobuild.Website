namespace Protobuild.Website.Models
{
    public class UserModel
    {
        public const string Kind = "user";

        public long Key { get; set; }

        public string GoogleId { get; set; }

        public string UniqueName { get; set; }

        public string CanonicalName { get; set; }

        public bool IsOrganisation { get; set; }

        public string ApiKey { get; set; }

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
            if (path == null)
            {
                return "/" + CanonicalName;
            }
            else
            {
                return "/" + CanonicalName + "/" + path;
            }
        }
    }
}
