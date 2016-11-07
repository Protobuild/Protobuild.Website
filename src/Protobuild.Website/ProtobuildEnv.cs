using System;

namespace Protobuild.Website
{
    public static class ProtobuildEnv
    {
        public static string GetDomain()
        {
            return Environment.GetEnvironmentVariable("DOMAIN")
                   ?? "http://localhost:57827";
        }
    }
}
