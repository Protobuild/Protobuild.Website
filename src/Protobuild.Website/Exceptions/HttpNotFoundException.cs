using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Threading.Tasks;

namespace Protobuild.Website.Exceptions
{
    public class HttpNotFoundException : HttpException
    {
        public HttpNotFoundException()
            : base(HttpStatusCode.NotFound)
        { }
    }
}
