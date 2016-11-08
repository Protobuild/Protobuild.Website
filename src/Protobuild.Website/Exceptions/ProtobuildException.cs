using System.Net;

namespace Protobuild.Website.Exceptions
{
    public class ProtobuildException : HttpException
    {
        public ProtobuildException(HttpStatusCode code, string message) : base(code, message)
        {
            ProtobuildMessage = message;
        }

        public string ProtobuildMessage { get; }
    }
}
