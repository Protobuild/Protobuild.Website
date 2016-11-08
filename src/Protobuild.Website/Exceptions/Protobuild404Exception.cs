using System.Net;

namespace Protobuild.Website.Exceptions
{
    public class Protobuild404Exception : ProtobuildException
    {
        public Protobuild404Exception(string message) : base(HttpStatusCode.NotFound, message)
        {
        }
    }
}
