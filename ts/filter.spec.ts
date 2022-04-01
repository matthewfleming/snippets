import { rejectNulls } from "./filter";

describe("filter", () => {
    it("works", () => {
        const test = {
            obj: {
                a: 'a',
                n: 1,
                nullArray: [null, 1, null, 2, null],
                nullObject: {
                    str: "str",
                    num: 1,
                    n: null,
                },
            },
        };

        expect(rejectNulls(test)).toMatchObject({
            obj: {
                a: 'a',
                n: 1,
                nullArray: [1, 2],
                nullObject: {
                    str: "str",
                    num: 1,
                },
            }
        });
    });
});
