using System.Net;

namespace Protobuild.Website.Exceptions
{
    public class Protobuild500Exception : ProtobuildException
    {
        public Protobuild500Exception(string message) : base(HttpStatusCode.InternalServerError, message)
        {
        }
    }
}
