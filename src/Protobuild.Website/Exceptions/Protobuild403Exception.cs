using System.Net;

namespace Protobuild.Website.Exceptions
{
    public class Protobuild403Exception : ProtobuildException
    {
        public Protobuild403Exception(string message) : base(HttpStatusCode.Forbidden, message)
        {
        }
    }
}
