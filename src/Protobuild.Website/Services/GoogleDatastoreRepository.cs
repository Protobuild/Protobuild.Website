using Google.Datastore.V1;
using System;
using Protobuild.Website.Models;
using System.Collections.Generic;
using System.Threading.Tasks;
using System.Linq;
using Protobuild.Website.Exceptions;

namespace Protobuild.Website.Services
{
    public class GoogleDatastoreRepository : IRepository
    {
        private DatastoreDb _db;

        private KeyFactory _userKeyFactory;

        private KeyFactory _packageKeyFactory;

        public GoogleDatastoreRepository()
        {
            Environment.SetEnvironmentVariable(
                "GOOGLE_APPLICATION_CREDENTIALS",
                ProtobuildEnv.GetServiceAccountPath());
            Environment.SetEnvironmentVariable(
                "GOOGLE_PROJECT_ID",
                ProtobuildEnv.GetProjectId());

            _db = DatastoreDb.Create(ProtobuildEnv.GetProjectId());
            _userKeyFactory = _db.CreateKeyFactory(UserModel.Kind);
            _packageKeyFactory = _db.CreateKeyFactory(PackageModel.Kind);
        }

        public async Task<IEnumerable<PackageModel>> LoadAllPackagesForUser(UserModel user)
        {
            throw new NotImplementedException();
        }

        public async Task<UserModel> LoadUserByName(string name)
        {
            if (name == null)
            {
                throw new NullReferenceException(nameof(name));
            }

            var query = new Query(UserModel.Kind)
            {
                Filter = Filter.Equal("canonicalName", name),
                Limit = 1
            };

            var result = _db.RunQueryLazilyAsync(query, ReadOptions.Types.ReadConsistency.Eventual);
            using (var enumerator = result.GetEnumerator())
            {
                while (await enumerator.MoveNext())
                {
                    var entity = enumerator.Current;

                    return MapUser(entity);
                }
            }

            throw new HttpNotFoundException();
        }

        public UserModel MapUser(Entity entity)
        {
            return new UserModel
            {
                Key = entity.Key.Path.Last().Id,
                GoogleId = entity["googleID"].StringValue,
                ApiKey = entity["apiKey"].StringValue,
                CanonicalName = entity["canonicalName"].StringValue,
                IsOrganisation = entity["isOrganisation"].BooleanValue,
                UniqueName = entity["uniqueName"].StringValue
            };
        }
    }
}
